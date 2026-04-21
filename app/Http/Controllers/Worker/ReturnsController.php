<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
        ];

        $query = LoanReturn::with('loan.user', 'loan.loanItems.item')
            ->where('status', 'partial')
            ->orderBy('created_at', 'desc')
            ;

        if ($filters['q'] !== '') {
            $search = $filters['q'];

            $query->whereHas('loan', function ($loanQuery) use ($search): void {
                $loanQuery->where('loan_code', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('kelas', 'like', '%' . $search . '%');
                    });
            });
        }

        $returns = $query->paginate(15)->withQueryString();

        return view('worker.returns.index', [
            'returns' => $returns,
            'filters' => $filters,
        ]);
    }

    public function show(LoanReturn $loanReturn): View
    {
        if ($loanReturn->status !== 'partial') {
            abort(404);
        }

        $loanReturn->load('loan.user', 'loan.loanItems.item');

        return view('worker.returns.show', [
            'loanReturn' => $loanReturn,
        ]);
    }

    public function accept(Request $request, LoanReturn $loanReturn): RedirectResponse
    {
        if ($loanReturn->status !== 'partial') {
            return redirect()->route('worker.returns.index')
                ->with('status', 'Pengembalian ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $loanReturn): void {
            /** @var Loan $loan */
            $loan = $loanReturn->loan()->lockForUpdate()->firstOrFail();

            $loanReturn->update([
                'status' => 'completed',
                'user_id' => (int) $request->user()->id,
                'notes' => $this->appendSystemNote(
                    $loanReturn->notes,
                    'Pengembalian diterima oleh petugas pada ' . now()->format('d-m-Y H:i') . '.'
                ),
            ]);

            $loan->returns()
                ->where('status', 'partial')
                ->where('id', '!=', $loanReturn->id)
                ->delete();

            $loan->update(['status' => 'completed']);
            $loan->loanItems()->update(['returned_quantity' => 1]);
        });

        return redirect()->route('worker.returns.index')
            ->with('status', 'Pengembalian berhasil diterima.');
    }

    public function reject(Request $request, LoanReturn $loanReturn): RedirectResponse
    {
        if ($loanReturn->status !== 'partial') {
            return redirect()->route('worker.returns.index')
                ->with('status', 'Pengembalian ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $loanReturn): void {
            /** @var Loan $loan */
            $loan = $loanReturn->loan()->lockForUpdate()->firstOrFail();

            $loanReturn->update([
                'status' => 'rejected',
                'user_id' => (int) $request->user()->id,
                'notes' => $this->appendSystemNote(
                    $loanReturn->notes,
                    'Pengembalian ditolak oleh petugas pada ' . now()->format('d-m-Y H:i') . '.'
                ),
            ]);

            $loan->update(['status' => 'borrowed']);
            $loan->loanItems()->update(['returned_quantity' => 0]);
        });

        return redirect()->route('worker.returns.index')
            ->with('status', 'Pengembalian ditandai sebagai ditolak.');
    }

    public function markDamaged(LoanReturn $loanReturn): RedirectResponse
    {
        if ($loanReturn->status !== 'partial') {
            return redirect()->route('worker.returns.index')
                ->with('status', 'Pengembalian ini sudah diproses sebelumnya.');
        }

        return redirect()->route('worker.returns.show', $loanReturn)
            ->with('status', 'Placeholder: alur barang rusak akan diproses oleh role Technician (belum tersedia).');
    }

    private function appendSystemNote(?string $currentNotes, string $note): string
    {
        $currentNotes = trim((string) $currentNotes);

        if ($currentNotes === '') {
            return $note;
        }

        return $currentNotes . PHP_EOL . $note;
    }
}
