<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    /**
     * Submit peminjaman baru (dari keranjang mobile).
     * POST /api/loans
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items'    => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'notes'    => ['nullable', 'string'],
        ], [
            'due_date.after_or_equal' => 'Tanggal pengembalian tidak boleh sebelum hari ini.'
        ]);

        try {
            return DB::transaction(function() use ($request) {
                // 1. Buat Header Peminjaman
                // Penyesuaian: user_id auto dari login, status fix pending, loan_date fix today.
                $loan = Loan::create([
                    'loan_code' => 'L-' . strtoupper(Str::random(8)),
                    'user_id'   => $request->user()->id, 
                    'loan_date' => now()->toDateString(),
                    'due_date'  => $request->due_date,
                    'status'    => 'pending',
                    'notes'     => $request->notes,
                ]);

                // 2. Simpan Item Detail
                foreach ($request->items as $cartItem) {
                    $item = Item::find($cartItem['id']);

                    // Validasi stok (Safety check)
                    if ($item->stock < $cartItem['quantity']) {
                        throw new \Exception("Stok alat {$item->name} tidak mencukupi.");
                    }

                    LoanItem::create([
                        'loan_id'  => $loan->id,
                        'item_id'  => $item->id,
                        'quantity' => $cartItem['quantity'],
                    ]);
                }

                return response()->json([
                    'message' => 'Peminjaman berhasil diajukan! Tunggu persetujuan petugas.',
                    'loan'    => $loan->load('loanItems.item'),
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ambil riwayat peminjaman user.
     */
    public function index(Request $request): JsonResponse
    {
        $loans = Loan::query()
            ->with(['loanItems.item', 'approver'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'loans' => $loans
        ]);
    }
}
