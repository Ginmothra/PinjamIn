<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoansController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
        ];

        $query = Loan::query()
            ->with(['user:id,name,email,kelas', 'loanItems.item:id,category_id,code,name'])
            ->withCount('loanItems')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ;

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

        $loans = $query->paginate(15)->withQueryString();

        return view('worker.loans.index', [
            'loans' => $loans,
            'filters' => $filters,
        ]);
    }

    public function approve(Request $request, Loan $loan): RedirectResponse
    {
        if ($loan->status !== 'pending') {
            return redirect()->route('worker.loans.index')
                ->with('status', 'Peminjaman ini sudah diproses.');
        }

        DB::transaction(function () use ($loan, $request): void {
            $loan->update([
                'status' => 'borrowed',
                'approved_by' => (int) $request->user()->id,
                'approved_at' => now(),
                'reject_reason' => null,
                'rejected_by' => null,
                'rejected_at' => null,
            ]);
        });

        return redirect()->route('worker.loans.index')
            ->with('status', 'Peminjaman berhasil di-approve.');
    }

    public function rejectForm(Loan $loan): View
    {
        if ($loan->status !== 'pending') {
            abort(404);
        }

        return view('worker.loans.reject', [
            'loan' => $loan->load(['user:id,name,email,kelas', 'loanItems.item:id,category_id,code,name']),
        ]);
    }

    public function reject(Request $request, Loan $loan): RedirectResponse
    {
        if ($loan->status !== 'pending') {
            return redirect()->route('worker.loans.index')
                ->with('status', 'Peminjaman ini sudah diproses.');
        }

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($loan, $request, $validated): void {
            $loan->update([
                'status' => 'rejected',
                'reject_reason' => $validated['reject_reason'],
                'rejected_by' => (int) $request->user()->id,
                'rejected_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
            ]);
        });

        return redirect()->route('worker.loans.index')
            ->with('status', 'Peminjaman berhasil ditolak.');
    }
}
