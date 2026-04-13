<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $query = Loan::query()
            ->with([
                'user:id,name,email,kelas',
                'approver:id,name',
            ])
            ->withSum('loanItems', 'quantity')
            ->latest();

        if ($filters['q'] !== '') {
            $search = $filters['q'];

            $query->where(function ($builder) use ($search): void {
                $builder->where('loan_code', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('kelas', 'like', '%' . $search . '%');
                    });
            });
        }

        if (array_key_exists($filters['status'], $this->statusLabels())) {
            $query->where('status', $filters['status']);
        }

        $loans = $query->paginate(15)->withQueryString();

        return view('admin.loans.index', [
            'loans' => $loans,
            'filters' => $filters,
            'statusLabels' => $this->statusLabels(),
            'statusBadges' => $this->statusBadges(),
        ]);
    }

    public function create(): View
    {
        return view('admin.loans.create', [
            'users' => $this->approvedUsers(),
            'items' => $this->availableItems(),
            'statusOptions' => $this->statusLabels(),
            'generatedLoanCode' => $this->generateLoanCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => $this->normalizeLoanItems($request->input('items', [])),
        ]);

        $validated = $request->validate([
            'loan_code' => ['required', 'string', 'max:255', Rule::unique(Loan::class)],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after:loan_date'],
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'required',
                'integer',
                'distinct',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->isItemAvailableForLoan((int) $value, null)) {
                        $fail('Barang sedang dipakai pada peminjaman aktif lain.');
                    }
                },
            ],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated): void {
            $loan = Loan::create([
                'loan_code' => $validated['loan_code'],
                'user_id' => $validated['user_id'],
                'loan_date' => $validated['loan_date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $loanItem) {
                LoanItem::create([
                    'loan_id' => $loan->id,
                    'item_id' => $loanItem['item_id'],
                    'quantity' => 1,
                    'notes' => $loanItem['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.loans.index')
            ->with('status', 'Data peminjaman berhasil dibuat.');
    }

    public function show(Loan $loan): View
    {
        return view('admin.loans.show', [
            'loan' => $loan->load([
                'user:id,name,email,kelas',
                'approver:id,name',
                'loanItems.item:id,code,name',
            ]),
            'statusLabels' => $this->statusLabels(),
            'statusBadges' => $this->statusBadges(),
        ]);
    }

    public function edit(Loan $loan): View
    {
        return view('admin.loans.edit', [
            'loan' => $loan->load(['loanItems.item:id,code,name']),
            'users' => $this->approvedUsers(),
            'items' => $this->availableItems($loan),
            'statusOptions' => $this->statusLabels(),
        ]);
    }

    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $request->merge([
            'items' => $this->normalizeLoanItems($request->input('items', [])),
        ]);

        $validated = $request->validate([
            'loan_code' => ['required', 'string', 'max:255', Rule::unique(Loan::class)->ignore($loan->id)],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'loan_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after:loan_date'],
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => [
                'required',
                'integer',
                'distinct',
                function (string $attribute, mixed $value, \Closure $fail) use ($loan): void {
                    if (! $this->isItemAvailableForLoan((int) $value, $loan)) {
                        $fail('Barang sedang dipakai pada peminjaman aktif lain.');
                    }
                },
            ],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($loan, $validated): void {
            $loan->update([
                'loan_code' => $validated['loan_code'],
                'user_id' => $validated['user_id'],
                'loan_date' => $validated['loan_date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $submittedItemIds = collect($validated['items'])
                ->pluck('item_id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            $loan->loanItems()->whereNotIn('item_id', $submittedItemIds)->delete();

            foreach ($validated['items'] as $loanItem) {
                $loan->loanItems()->updateOrCreate(
                    ['item_id' => $loanItem['item_id']],
                    [
                        'quantity' => 1,
                        'notes' => $loanItem['notes'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('admin.loans.index')
            ->with('status', 'Data peminjaman berhasil diperbarui.');
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        if ($loan->returns()->exists()) {
            return redirect()->route('admin.loans.index')
                ->with('status', 'Peminjaman tidak dapat dihapus karena sudah memiliki data pengembalian.');
        }

        $loan->delete();

        return redirect()->route('admin.loans.index')
            ->with('status', 'Data peminjaman berhasil dihapus.');
    }

    private function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'borrowed' => 'Dipinjam',
            'returned' => 'Dikembalikan',
        ];
    }

    private function statusBadges(): array
    {
        return [
            'pending' => 'bg-amber-400 text-slate-900 ring-amber-400',
            'borrowed' => 'bg-rose-600 text-white ring-rose-600',
            'returned' => 'bg-emerald-600 text-white ring-emerald-600',
        ];
    }

    private function approvedUsers()
    {
        return User::query()
            ->where('status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'kelas']);
    }

    private function availableItems(?Loan $loan = null)
    {
        $blockedStatuses = ['pending', 'borrowed', 'returned'];

        $items = Item::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->whereDoesntHave('loanItems.loan', function ($builder) use ($blockedStatuses, $loan): void {
                $builder->whereIn('status', $blockedStatuses);

                if ($loan !== null) {
                    $builder->where('loans.id', '!=', $loan->id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'category_id', 'code', 'name']);

        if ($loan !== null) {
            $currentItems = Item::query()
                ->with('category:id,name')
                ->whereIn('id', $loan->loanItems()->pluck('item_id'))
                ->orderBy('name')
                ->get(['id', 'category_id', 'code', 'name']);

            return $items->merge($currentItems)->unique('id')->values();
        }

        return $items;
    }

    private function isItemAvailableForLoan(int $itemId, ?Loan $loan = null): bool
    {
        $blockedStatuses = ['pending', 'borrowed', 'returned'];

        $query = Item::query()
            ->whereKey($itemId)
            ->where('is_active', true)
            ->whereDoesntHave('loanItems.loan', function ($builder) use ($blockedStatuses, $loan): void {
                $builder->whereIn('status', $blockedStatuses);

                if ($loan !== null) {
                    $builder->where('loans.id', '!=', $loan->id);
                }
            });

        return $query->exists();
    }

    private function normalizeLoanItems(array $items): array
    {
        return collect($items)
            ->map(static function ($item): array {
                return [
                    'item_id' => isset($item['item_id']) ? (int) $item['item_id'] : null,
                    'notes' => isset($item['notes']) ? trim((string) $item['notes']) : null,
                    'quantity' => 1,
                ];
            })
            ->filter(static function (array $item): bool {
                return ! empty($item['item_id']) || ! empty($item['notes']);
            })
            ->values()
            ->all();
    }

    private function generateLoanCode(): string
    {
        $date = now()->format('Ymd');
        $sequence = Loan::query()->whereDate('created_at', now()->toDateString())->count() + 1;

        do {
            $loanCode = sprintf('PMJ-%s-%03d', $date, $sequence);
            $sequence++;
        } while (Loan::query()->where('loan_code', $loanCode)->exists());

        return $loanCode;
    }
}
