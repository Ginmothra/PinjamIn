<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanReturnManagementController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureReturnedLoansHaveReturnRecords((int) $request->user()->id);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $query = LoanReturn::query()
            ->with([
                'loan:id,loan_code,user_id,status',
                'loan.user:id,name,email',
                'user:id,name',
            ])
            ->latest();

        if ($filters['q'] !== '') {
            $search = $filters['q'];

            $query->where(function ($builder) use ($search): void {
                $builder->whereHas('loan', function ($loanQuery) use ($search): void {
                    $loanQuery->where('loan_code', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            });
        }

        if (array_key_exists($filters['status'], $this->statusLabels())) {
            $query->where('status', $filters['status']);
        }

        $returns = $query->paginate(15)->withQueryString();

        return view('admin.returns.index', [
            'returns' => $returns,
            'filters' => $filters,
            'statusLabels' => $this->statusLabels(),
            'statusBadges' => $this->statusBadges(),
        ]);
    }

    public function create(): View
    {
        return view('admin.returns.create', [
            'returnableLoans' => $this->returnableLoans(),
            'statusOptions' => $this->statusLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'loan_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->isLoanReturnable((int) $value)) {
                        $fail('Peminjaman ini tidak tersedia untuk pengembalian.');
                    }
                },
            ],
            'return_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            LoanReturn::create([
                'loan_id' => $validated['loan_id'],
                'user_id' => (int) $request->user()->id,
                'return_date' => $validated['return_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $loan = Loan::query()->findOrFail($validated['loan_id']);
            $this->syncLoanCompletionState($loan);
        });

        return redirect()->route('admin.returns.index')
            ->with('status', 'Data pengembalian berhasil dibuat.');
    }

    public function show(LoanReturn $loanReturn): View
    {
        return view('admin.returns.show', [
            'loanReturn' => $loanReturn->load([
                'loan.user:id,name,email,kelas',
                'loan.loanItems.item:id,code,name',
                'user:id,name',
            ]),
            'statusLabels' => $this->statusLabels(),
            'statusBadges' => $this->statusBadges(),
        ]);
    }

    public function edit(LoanReturn $loanReturn): View
    {
        return view('admin.returns.edit', [
            'loanReturn' => $loanReturn->load(['loan.user:id,name,email,kelas']),
            'statusOptions' => $this->statusLabels(),
        ]);
    }

    public function update(Request $request, LoanReturn $loanReturn): RedirectResponse
    {
        $validated = $request->validate([
            'return_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($loanReturn, $validated): void {
            $loanReturn->update([
                'return_date' => $validated['return_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $loanReturn->loan()->lockForUpdate()->first();
            $this->syncLoanCompletionState($loanReturn->loan);
        });

        return redirect()->route('admin.returns.index')
            ->with('status', 'Data pengembalian berhasil diperbarui.');
    }

    public function destroy(LoanReturn $loanReturn): RedirectResponse
    {
        DB::transaction(function () use ($loanReturn): void {
            $loan = $loanReturn->loan;
            $loanReturn->delete();

            if ($loan !== null) {
                $this->syncLoanCompletionState($loan);
            }
        });

        return redirect()->route('admin.returns.index')
            ->with('status', 'Data pengembalian berhasil dihapus.');
    }

    private function statusLabels(): array
    {
        return [
            'partial' => 'Pending',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
        ];
    }

    private function statusBadges(): array
    {
        return [
            'partial' => 'bg-orange-500 text-white ring-orange-500',
            'pending' => 'bg-orange-500 text-white ring-orange-500',
            'completed' => 'bg-emerald-600 text-white ring-emerald-600',
            'rejected' => 'bg-rose-600 text-white ring-rose-600',
        ];
    }

    private function returnableLoans()
    {
        return Loan::query()
            ->with('user:id,name,email,kelas')
            ->where('status', 'borrowed')
            ->orderByDesc('loan_date')
            ->get(['id', 'loan_code', 'user_id', 'loan_date', 'due_date']);
    }

    private function isLoanReturnable(int $loanId): bool
    {
        return Loan::query()
            ->whereKey($loanId)
            ->where('status', 'borrowed')
            ->exists();
    }

    private function syncLoanCompletionState(Loan $loan): void
    {
        $hasPendingReturn = $loan->returns()->where('status', 'partial')->exists();
        $hasCompletedReturn = $loan->returns()->where('status', 'completed')->exists();

        if ($hasCompletedReturn) {
            // Finalized return should collapse pending duplicates for the same loan.
            $loan->returns()->where('status', 'partial')->delete();

            $loan->update(['status' => 'completed']);
            $loan->loanItems()->update(['returned_quantity' => 1]);

            return;
        }

        if ($hasPendingReturn) {
            $loan->update(['status' => 'returned']);
            $loan->loanItems()->update(['returned_quantity' => 0]);

            return;
        }

        $loan->update(['status' => 'borrowed']);
        $loan->loanItems()->update(['returned_quantity' => 0]);
    }

    private function ensureReturnedLoansHaveReturnRecords(int $processedByUserId): void
    {
        Loan::query()
            ->whereIn('status', ['returned', 'completed'])
            ->whereDoesntHave('returns')
            ->with('returns:id,loan_id,status')
            ->get(['id', 'due_date', 'status'])
            ->each(function (Loan $loan) use ($processedByUserId): void {
                $returnStatus = $loan->status === 'completed' ? 'completed' : 'partial';

                LoanReturn::create([
                    'loan_id' => $loan->id,
                    'user_id' => $processedByUserId,
                    'return_date' => optional($loan->due_date)->format('Y-m-d') ?? now()->toDateString(),
                    'status' => $returnStatus,
                    'notes' => $returnStatus === 'completed'
                        ? 'Data pengembalian selesai dibuat otomatis dari status peminjaman selesai.'
                        : 'Data pending pengembalian dibuat otomatis dari status peminjaman dikembalikan.',
                ]);

                $loan->loanItems()->update([
                    'returned_quantity' => $returnStatus === 'completed' ? 1 : 0,
                ]);
            });
    }
}
