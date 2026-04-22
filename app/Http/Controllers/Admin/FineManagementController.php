<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Fine;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FineManagementController extends Controller
{
    public function index(Request $request): View
    {
        // Get users with at least one fine, or search users
        $query = User::role('user')
            ->withCount(['fines as unpaid_fines_count' => function ($query) {
                $query->where('status', 'unpaid');
            }])
            ->withSum(['fines as unpaid_fines_sum' => function ($query) {
                $query->where('status', 'unpaid');
            }], 'amount');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $users = $query->paginate(15);

        return view('admin.fines.index', compact('users'));
    }

    public function show(User $user): View
    {
        $fines = $user->fines()->latest()->get();
        $unpaidSum = $user->fines()->where('status', 'unpaid')->sum('amount');
        
        // Ambil peminjaman user untuk dropdown, urutkan dari yang terbaru
        $loans = $user->loans()->latest()->get();

        return view('admin.fines.show', compact('user', 'fines', 'unpaidSum', 'loans'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:1000'],
            'loan_id' => ['nullable', 'exists:loans,id'],
        ]);

        $user->fines()->create([
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'loan_id' => $validated['loan_id'],
            'status' => 'unpaid',
        ]);

        return redirect()->route('admin.fines.show', $user)->with('status', 'Denda berhasil ditambahkan.');
    }

    public function markPaid(Fine $fine): RedirectResponse
    {
        $fine->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('status', 'Denda ditandai sebagai lunas.');
    }

    public function destroy(Fine $fine): RedirectResponse
    {
        $fine->delete();

        return redirect()->back()->with('status', 'Denda berhasil dihapus.');
    }
}
