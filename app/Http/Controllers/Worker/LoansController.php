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
            'tab' => $request->query('tab', 'antrean'), // Default tab is antrean (pending)
        ];

        $query = Loan::query()
            ->with(['user:id,name,email,kelas', 'loanItems.item:id,category_id,code,name'])
            ->withCount('loanItems');

        if ($filters['tab'] === 'booking') {
            $query->where('status', 'booked');
        } else {
            $query->where('status', 'pending');
        }

        $query->orderBy('created_at', 'desc');

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
        if ($loan->status !== 'pending' && $loan->status !== 'booked') {
            return redirect()->route('worker.loans.index')
                ->with('status', 'Peminjaman ini sudah diproses.');
        }

        $message = $loan->status === 'booked' ? 'Barang booking berhasil diserahkan.' : 'Peminjaman berhasil di-approve.';

        try {
            // Menggunakan Stored Procedure untuk approve peminjaman
            DB::statement("CALL sp_approve_peminjaman(?, ?)", [$loan->id, $request->user()->id]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses peminjaman: ' . $e->getMessage());
        }

        return redirect()->route('worker.loans.index', ['tab' => $request->query('tab', 'antrean')])
            ->with('status', $message);
    }

    public function rejectForm(Loan $loan): View
    {
        if ($loan->status !== 'pending' && $loan->status !== 'booked') {
            abort(404);
        }

        return view('worker.loans.reject', [
            'loan' => $loan->load(['user:id,name,email,kelas', 'loanItems.item:id,category_id,code,name']),
        ]);
    }

    public function reject(Request $request, Loan $loan): RedirectResponse
    {
        if ($loan->status !== 'pending' && $loan->status !== 'booked') {
            return redirect()->route('worker.loans.index')
                ->with('status', 'Peminjaman ini sudah diproses.');
        }

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            $loan->update([
                'status' => 'rejected',
                'reject_reason' => $validated['reject_reason'],
                'rejected_by' => (int) $request->user()->id,
                'rejected_at' => now(),
                'approved_by' => null,
                'approved_at' => null,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menolak peminjaman.');
        }

        return redirect()->route('worker.loans.index', ['tab' => $request->query('tab', 'antrean')])
            ->with('status', 'Peminjaman berhasil ditolak.');
    }
}
