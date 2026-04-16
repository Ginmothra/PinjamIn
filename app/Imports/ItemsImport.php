<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToCollection, WithHeadingRow
{
    private array $validConditions = ['good', 'maintenance', 'damaged'];
    private array $errors = [];

    public function collection(Collection $rows): void
    {
        $rowNumber = 2;
        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $categoryName = trim((string) ($row['category_name'] ?? ''));

            if ($code === '' || $name === '' || $categoryName === '') {
                if ($code === '') {
                    $this->errors[] = ['row' => $rowNumber, 'field' => 'code', 'message' => 'Kode barang wajib diisi.'];
                }
                if ($name === '') {
                    $this->errors[] = ['row' => $rowNumber, 'field' => 'name', 'message' => 'Nama barang wajib diisi.'];
                }
                if ($categoryName === '') {
                    $this->errors[] = ['row' => $rowNumber, 'field' => 'category_name', 'message' => 'Nama kategori wajib diisi.'];
                }
                $rowNumber++;
                continue;
            }

            $category = Category::query()->firstOrCreate(
                ['name' => $categoryName],
                ['is_active' => true],
            );

            $item = Item::query()->firstOrNew(['code' => $code]);
            $item->category_id = $category->id;
            $item->name = $name;
            $item->description = $this->nullableText($row['description'] ?? null);
            $item->condition = $this->normalizeCondition($row['condition'] ?? null);
            $item->stock = 1;
            $item->is_active = $this->toBoolean($row['is_active'] ?? 1);

            if (! $item->exists) {
                $item->image_path = null;
            }

            $item->save();
            $rowNumber++;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function normalizeCondition(mixed $value): string
    {
        $condition = strtolower(trim((string) $value));

        if (! in_array($condition, $this->validConditions, true)) {
            return 'good';
        }

        return $condition;
    }

    private function toBoolean(mixed $value, bool $default = true): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'y', 'ya', 'aktif'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'n', 'tidak', 'nonaktif'], true)) {
            return false;
        }

        return $default;
    }
}
