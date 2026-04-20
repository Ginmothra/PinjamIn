<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\LoanReturn;
use Illuminate\View\View;

class ReturnsController extends Controller
{
    public function index(): View
    {
        $returns = LoanReturn::with('loan.user', 'loan.loanItems.item')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('worker.returns.index', [
            'returns' => $returns,
        ]);
    }

    public function show(LoanReturn $loanReturn): View
    {
        $loanReturn->load('loan.user', 'loan.loanItems.item');

        return view('worker.returns.show', [
            'loanReturn' => $loanReturn,
        ]);
    }
}
