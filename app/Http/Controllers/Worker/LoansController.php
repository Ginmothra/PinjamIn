<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            // Booking: Semua yang tanggalnya di masa depan (baik pending maupun sudah booked)
            $query->where(function ($q) {
                $q->where('status', 'booked')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'pending')
                         ->whereDate('loan_date', '>', now()->toDateString());
                  });
            });
        } else {
            // Antrean: Pending hari ini (atau lampau) + Booked yang harus diambil hari ini
            $query->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->where('status', 'pending')
                       ->whereDate('loan_date', '<=', now()->toDateString());
                })->orWhere(function ($sq) {
                    $sq->where('status', 'booked')
                       ->whereDate('loan_date', '<=', now()->toDateString());
                });
            });
        }

        $query->orderBy('created_at', 'desc');

        if ($filters['q'] !== '') {
            $search = $filters['q'];

            $query->where(function ($builder) use ($search): void {
                $builder->where('loan_code', 'like', '%' . $search . '%')
                    ->orWhere('verification_code', 'like', '%' . $search . '%')
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

        try {
            // Jika masih pending (Baru masuk), panggil Stored Procedure
            // SP ini otomatis potong stok & tentukan status (booked/borrowed)
            if ($loan->status === 'pending') {
                DB::statement("CALL sp_approve_peminjaman(?, ?)", [$loan->id, $request->user()->id]);
                $loan->refresh();

                // Jika statusnya jadi booked atau borrowed, generate kode verifikasi
                if (in_array($loan->status, ['booked', 'borrowed'])) {
                    $loan->update(['verification_code' => strtoupper(Str::random(6))]);
                }

                $message = $loan->status === 'booked' ? 'Booking dikonfirmasi & stok barang telah diamankan.' : 'Peminjaman berhasil di-approve.';
            } else {
                // Jika status sudah booked (User datang ambil barang di hari H)
                // Cukup update status jadi borrowed (stok sudah dipotong saat booking konfirmasi)
                $loan->update(['status' => 'borrowed']);
                $message = 'Barang booking berhasil diserahkan.';
            }
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
