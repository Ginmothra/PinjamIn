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

        DB::beginTransaction();
        try {
            /** @var Loan $loan */
            $loan = $loanReturn->loan()->lockForUpdate()->firstOrFail();

            // Memanggil Stored Procedure untuk Selesaikan Pengembalian & Restock
            DB::statement("CALL sp_selesaikan_pengembalian(?, ?, ?)", [
                $loan->id,
                $loanReturn->id,
                (int) $request->user()->id
            ]);

            // Tambahkan sistem note secara manual di PHP karena SP cuma urusin data inti
            $loanReturn->refresh();
            $loanReturn->update([
                'notes' => $this->appendSystemNote(
                    $loanReturn->notes,
                    'Pengembalian diterima oleh petugas pada ' . now()->format('d-m-Y H:i') . '.'
                ),
            ]);

            // Hapus pengajuan return lain yang menggantung buat loan ini
            $loan->returns()
                ->where('status', 'partial')
                ->where('id', '!=', $loanReturn->id)
                ->delete();

            $loan->loanItems()->update(['returned_quantity' => 1]);

            DB::commit();
            return redirect()->route('worker.returns.index')
                ->with('status', 'Pengembalian berhasil diterima.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    public function rejectForm(LoanReturn $loanReturn): View
    {
        if ($loanReturn->status !== 'partial') {
            abort(404);
        }

        return view('worker.returns.reject', [
            'loanReturn' => $loanReturn->load('loan.user', 'loan.loanItems.item'),
        ]);
    }

    public function reject(Request $request, LoanReturn $loanReturn): RedirectResponse
    {
        if ($loanReturn->status !== 'partial') {
            return redirect()->route('worker.returns.index')
                ->with('status', 'Pengembalian ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();
        try {
            /** @var Loan $loan */
            $loan = $loanReturn->loan()->lockForUpdate()->firstOrFail();

            $loanReturn->update([
                'status' => 'rejected',
                'user_id' => (int) $request->user()->id,
                'notes' => $this->appendSystemNote(
                    $loanReturn->notes,
                    'DITOLAK: ' . $validated['notes']
                ),
            ]);

            // Balikkan status loan ke borrowed agar user bisa upload ulang
            $loan->update(['status' => 'borrowed']);
            $loan->loanItems()->update(['returned_quantity' => 0]);

            DB::commit();
            return redirect()->route('worker.returns.index')
                ->with('status', 'Pengembalian berhasil ditolak dengan alasan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menolak pengembalian.');
        }
    }

    public function damagedForm(LoanReturn $loanReturn): View
    {
        if ($loanReturn->status !== 'partial') {
            abort(404);
        }

        return view('worker.returns.damaged', [
            'return' => $loanReturn->load('loan.user', 'loan.loanItems.item'),
        ]);
    }

    public function markDamaged(Request $request, LoanReturn $loanReturn): RedirectResponse
    {
        if ($loanReturn->status !== 'partial') {
            return redirect()->route('worker.returns.index')
                ->with('status', 'Pengembalian ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:1000'],
            'damaged_items' => ['required', 'array', 'min:1'],
            'damaged_items.*' => ['exists:items,id'],
        ]);

        DB::beginTransaction();
        try {
            /** @var Loan $loan */
            $loan = $loanReturn->loan()->lockForUpdate()->firstOrFail();

            // Memanggil Stored Procedure untuk Selesaikan Pengembalian & Restock
            DB::statement("CALL sp_selesaikan_pengembalian(?, ?, ?)", [
                $loan->id,
                $loanReturn->id,
                (int) $request->user()->id
            ]);

            // Tambahkan catatan kerusakan di level PHP
            $loanReturn->refresh();
            $loanReturn->update([
                'notes' => $this->appendSystemNote(
                    $loanReturn->notes,
                    'KONDISI RUSAK (' . count($validated['damaged_items']) . ' barang): ' . $validated['description']
                ),
            ]);

            // Update condition barang jadi damaged & buat repair record HANYA untuk yang dipilih
            foreach ($loan->loanItems as $loanItem) {
                if (in_array($loanItem->item_id, $validated['damaged_items'])) {
                    $item = $loanItem->item;
                    $item->update(['condition' => 'damaged']);

                    \App\Models\Repair::create([
                        'item_id' => $item->id,
                        'loan_return_id' => $loanReturn->id,
                        'description' => $validated['description'],
                        'status' => 'pending',
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('worker.returns.index')
                ->with('status', 'Laporan kerusakan berhasil dikirim ke Teknisi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses kerusakan barang: ' . $e->getMessage());
        }
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
