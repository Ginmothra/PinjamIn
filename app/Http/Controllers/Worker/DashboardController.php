<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanReturn;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $loansCount = Loan::count();
        $loansOngoing = Loan::whereIn('status', ['pending', 'borrowed'])->count();
        $returnsCount = LoanReturn::count();
        $returnsPending = LoanReturn::where('status', 'pending')->count();

        return view('worker.dashboard', [
            'loansCount' => $loansCount,
            'loansOngoing' => $loansOngoing,
            'returnsCount' => $returnsCount,
            'returnsPending' => $returnsPending,
        ]);
    }
}
