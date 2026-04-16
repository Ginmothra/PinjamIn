<?php

namespace App\Exports;

use App\Models\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemsExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return Item::query()
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(static fn (Item $item): array => [
                'code' => $item->code,
                'name' => $item->name,
                'category_name' => $item->category?->name,
                'description' => $item->description,
                'condition' => $item->condition,
                'is_active' => $item->is_active ? 1 : 0,
            ]);
    }

    public function headings(): array
    {
        return ['code', 'name', 'category_name', 'description', 'condition', 'is_active'];
    }
}
