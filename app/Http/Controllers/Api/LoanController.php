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
            'type'     => ['nullable', 'string', 'in:loan,booking'],
        ], [
            'due_date.after_or_equal' => 'Tanggal pengembalian tidak boleh sebelum hari ini.'
        ]);

        $isBooking = $request->type === 'booking';

        DB::beginTransaction();
        try {
            // 1. Buat Header Peminjaman
            $loan = Loan::create([
                'loan_code' => 'L-' . strtoupper(Str::random(8)),
                'user_id'   => $request->user()->id, 
                'loan_date' => $isBooking ? now()->addDay()->toDateString() : now()->toDateString(),
                'due_date'  => $request->due_date,
                'status'    => $isBooking ? 'booked' : 'pending',
                'notes'     => $request->notes,
            ]);

            // 2. Simpan Item Detail
            foreach ($request->items as $cartItem) {
                $item = Item::findOrFail($cartItem['id']);

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

            DB::commit();

            return response()->json([
                'message' => $isBooking ? 'Booking berhasil diajukan!' : 'Peminjaman berhasil diajukan! Tunggu persetujuan petugas.',
                'loan'    => $loan->load('loanItems.item'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
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
