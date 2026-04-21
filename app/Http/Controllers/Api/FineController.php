<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FineController extends Controller
{
    /**
     * Get user fine history.
     */
    public function index(Request $request): JsonResponse
    {
        $fines = $request->user()->fines()->latest()->get();

        return response()->json([
            'fines' => $fines
        ]);
    }
}
