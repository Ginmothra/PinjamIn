<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Ambil daftar alat yang tersedia (stok > 0 dan aktif).
     * GET /api/items
     */
    public function index(Request $request): JsonResponse
    {
        $items = Item::query()
            ->with('category')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where('condition', 'good')
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return response()->json([
            'items' => $items->map(function($item) {
                return [
                    'id'          => $item->id,
                    'category'    => $item->category->name ?? 'Tanpa Kategori',
                    'code'        => $item->code,
                    'name'        => $item->name,
                    'description' => $item->description,
                    'stock'       => $item->stock,
                    'image'       => $item->image_path 
                                        ? (Str::startsWith($item->image_path, ['http://', 'https://']) ? $item->image_path : asset('storage/' . $item->image_path)) 
                                        : null,
                ];
            })
        ]);
    }
}
