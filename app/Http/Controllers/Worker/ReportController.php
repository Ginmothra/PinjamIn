<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanReturn;
use App\Models\Item;
use Illuminate\Http\Request;
use Barrier\DomPDF\Facade\Pdf; // Package barryvdh/laravel-dompdf
use Carbon\Carbon;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end   = Carbon::parse($request->end_date)->endOfDay();

        // 1. Data Peminjaman
        $loans = Loan::with(['user', 'loanItems.item'])
            ->whereBetween('loan_date', [$start, $end])
            ->get();

        // 2. Data Pengembalian
        $returns = LoanReturn::with(['loan.user', 'loan.loanItems.item'])
            ->whereBetween('return_date', [$start, $end])
            ->get();

        // 3. Data Kerusakan (Berdasarkan histori perbaikan teknisi)
        $damaged = \App\Models\Repair::with(['item', 'loanReturn.loan.user'])
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $data = [
            'title'      => 'Laporan Harian PinjamIn',
            'date_range' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
            'loans'      => $loans,
            'returns'    => $returns,
            'damaged'    => $damaged,
            'generated_at' => now()->format('d M Y H:i'),
            'worker'     => auth()->user()->name
        ];

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily', $data);
        
        return $pdf->download('Laporan_PinjamIn_' . $start->format('Ymd') . '.pdf');
    }
}
