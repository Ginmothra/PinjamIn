<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemManagementController extends Controller
{
    public function index(): View
    {
        $items = Item::query()
            ->with([
                'loanItems.loan:id,status',
            ])
            ->latest()
            ->paginate(15);

        return view('admin.items.index', [
            'items' => $items,
            'itemStatusBadges' => $this->itemStatusBadges(),
        ]);
    }

    public function show(Item $item): View
    {
        return view('admin.items.show', [
            'item' => $item->load(['category']),
            'conditionLabels' => $this->conditionLabels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.items.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'conditionLabels' => $this->conditionLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'code' => ['required', 'string', 'max:255', Rule::unique(Item::class)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'condition' => ['required', Rule::in(array_keys($this->conditionLabels()))],
            'is_active' => ['boolean'],
        ]);

        $imagePath = $request->file('image')->store('items', 'public');

        Item::create([
            'category_id' => $validated['category_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'stock' => 1,
            'condition' => $validated['condition'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.items.index')
            ->with('status', 'Barang baru berhasil dibuat.');
    }

    public function edit(Item $item): View
    {
        return view('admin.items.edit', [
            'item' => $item,
            'categories' => Category::query()->orderBy('name')->get(),
            'conditionLabels' => $this->conditionLabels(),
        ]);
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'code' => ['required', 'string', 'max:255', Rule::unique(Item::class)->ignore($item->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'condition' => ['required', Rule::in(array_keys($this->conditionLabels()))],
            'is_active' => ['boolean'],
        ]);

        $imagePath = $item->image_path;

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store('items', 'public');
        }

        $item->update([
            'category_id' => $validated['category_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'stock' => 1,
            'condition' => $validated['condition'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.items.index')
            ->with('status', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $loanItemCount = $item->loanItems()->count();

        if ($loanItemCount > 0) {
            return redirect()->route('admin.items.index')
                ->with('status', 'Barang tidak dapat dihapus karena sudah memiliki riwayat peminjaman.');
        }

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()->route('admin.items.index')
            ->with('status', 'Barang berhasil dihapus.');
    }

    private function conditionLabels(): array
    {
        return [
            'good' => 'Baik',
            'maintenance' => 'Perawatan',
            'damaged' => 'Rusak',
        ];
    }

    private function conditionBadges(): array
    {
        return [
            'good' => 'bg-emerald-600 text-white ring-emerald-600',
            'maintenance' => 'bg-amber-400 text-slate-900 ring-amber-400',
            'damaged' => 'bg-rose-600 text-white ring-rose-600',
        ];
    }

    private function itemStatusBadges(): array
    {
        return [
            'aktif' => 'bg-emerald-600 text-white ring-emerald-600',
            'dipinjam' => 'bg-rose-600 text-white ring-rose-600',
            'dikembalikan' => 'bg-amber-400 text-slate-900 ring-amber-400',
        ];
    }
}
