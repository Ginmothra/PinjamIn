<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Repair;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepairController extends Controller
{
    public function index(Request $request): View
    {
        $repairs = Repair::with(['item.category', 'loanReturn.loan.user'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('status', 'desc') // in_progress first
            ->orderBy('created_at', 'asc')
            ->get();

        return view('technician.dashboard', [
            'repairs' => $repairs,
        ]);
    }

    public function history(Request $request): View
    {
        $history = Repair::with(['item.category', 'loanReturn.loan.user'])
            ->where('status', 'completed')
            ->where('technician_id', $request->user()->id)
            ->latest('completed_at')
            ->paginate(15);

        return view('technician.history', [
            'history' => $history,
        ]);
    }

    public function startRepair(Repair $repair): RedirectResponse
    {
        if ($repair->status !== 'pending') {
            return redirect()->back()->with('error', 'Perbaikan sudah dimulai.');
        }

        DB::beginTransaction();
        try {
            $repair->update([
                'status' => 'in_progress',
                'technician_id' => auth()->id(),
                'started_at' => now(),
            ]);

            $repair->item->update(['condition' => 'maintenance']);

            DB::commit();
            return redirect()->route('technician.dashboard')->with('status', 'Pengerjaan perbaikan dimulai.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memulai perbaikan.');
        }
    }

    public function updateProgress(Request $request, Repair $repair): RedirectResponse
    {
        $validated = $request->validate([
            'repair_notes' => ['required', 'string', 'max:1000'],
        ]);

        $repair->update([
            'repair_notes' => $validated['repair_notes'],
        ]);

        return redirect()->route('technician.dashboard')->with('status', 'Progress perbaikan diperbarui.');
    }

    public function completeRepair(Request $request, Repair $repair): RedirectResponse
    {
        if ($repair->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Perbaikan belum dimulai.');
        }

        DB::beginTransaction();
        try {
            $repair->update([
                'status' => 'completed',
                'repair_notes' => $request->repair_notes ?? $repair->repair_notes,
                'completed_at' => now(),
            ]);

            $repair->item->update(['condition' => 'good']);

            DB::commit();
            return redirect()->route('technician.dashboard')->with('status', 'Barang berhasil diperbaiki dan siap digunakan kembali.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyelesaikan perbaikan.');
        }
    }
}
