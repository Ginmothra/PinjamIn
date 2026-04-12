<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Loan;
use App\Models\LoanReturn;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $loanStatusCounts = Loan::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'stats' => [
                'ongoing_loans' => Loan::query()->whereIn('status', ['approved', 'borrowed'])->count(),
                'pending_loans' => Loan::query()->where('status', 'pending')->count(),
                'returns_total' => LoanReturn::query()->count(),
                'total_items' => Item::query()->count(),
                'total_categories' => Category::query()->count(),
                'total_users' => User::query()->count(),
            ],
            'loanStatusCounts' => $loanStatusCounts,
        ]);
    }
}
