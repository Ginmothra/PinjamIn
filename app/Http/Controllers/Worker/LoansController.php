<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\View\View;

class LoansController extends Controller
{
    public function index(): View
    {
        $loans = Loan::with('user', 'loanItems.item')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('worker.loans.index', [
            'loans' => $loans,
        ]);
    }

    public function show(Loan $loan): View
    {
        $loan->load('user', 'loanItems.item', 'loanReturns');

        return view('worker.loans.show', [
            'loan' => $loan,
        ]);
    }
}
