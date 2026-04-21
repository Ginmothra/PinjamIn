<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    /**
     * Ambil daftar peminjaman yang bisa dikembalikan (status approved).
     */
    public function getActiveLoans(Request $request): JsonResponse
    {
        $loans = Loan::query()
            ->with(['loanItems.item'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'borrowed')
            ->latest()
            ->get();

        return response()->json(['loans' => $loans]);
    }

    /**
     * Submit pengembalian baru.
     * POST /api/returns
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'loan_id'     => ['required', 'exists:loans,id'],
            'proof_image' => ['required', 'image', 'max:2048'], // Wajib foto, max 2MB
            'notes'       => ['nullable', 'string'],
        ]);

        $loan = Loan::where('id', $request->loan_id)
                    ->where('user_id', $request->user()->id)
                    ->firstOrFail();

        // Pastikan belum pernah diajukan pengembalian sebelumnya
        $exists = LoanReturn::where('loan_id', $loan->id)->exists();
        if ($exists) {
            return response()->json(['message' => 'Peminjaman ini sudah dalam proses pengembalian.'], 422);
        }

        try {
            return DB::transaction(function() use ($loan, $request) {
                $proofImagePath = null;
                if ($request->hasFile('proof_image')) {
                    $file = $request->file('proof_image');
                    $filename = 'return_' . time() . '_' . $loan->loan_code . '.' . $file->getClientOriginalExtension();
                    $proofImagePath = $file->storeAs('returns', $filename, 'public');
                }

                $return = LoanReturn::create([
                    'loan_id'     => $loan->id,
                    'return_date' => now(),
                    'status'      => 'partial',
                    'proof_image' => $proofImagePath ? '/storage/' . $proofImagePath : null,
                    'notes'       => $request->notes,
                ]);

                return response()->json([
                    'message' => 'Pengembalian berhasil diajukan! Bukti foto telah tersimpan.',
                    'return'  => $return
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Ambil riwayat pengembalian user.
     */
    public function index(Request $request): JsonResponse
    {
        $returns = LoanReturn::query()
            ->with(['loan.loanItems.item', 'approver'])
            ->whereHas('loan', function($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->latest()
            ->get();

        return response()->json(['returns' => $returns]);
    }
}
