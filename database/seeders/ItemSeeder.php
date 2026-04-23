<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Elektronik' => 'Peralatan elektronik untuk mendukung kegiatan.',
            'Alat Tulis' => 'Kebutuhan kantor dan alat tulis.',
            'Furnitur' => 'Meja, kursi, dan perabotan lainnya.',
            'Olahraga' => 'Peralatan olahraga dan kesehatan.',
            'Kebersihan' => 'Alat-alat penunjang kebersihan lingkungan.',
        ];

        foreach ($categories as $name => $desc) {
            $category = Category::create([
                'name' => $name,
                'description' => $desc,
                'is_active' => true,
            ]);

            // Buat 1 barang untuk setiap kategori biar total ada 5 barang
            Item::create([
                'category_id' => $category->id,
                'code' => 'BRG-' . strtoupper(Str::random(5)),
                'name' => 'Proyektor' . ' ' . rand(1, 100),
                'description' => 'Ini adalah deskripsi untuk ' . $name . ' yang berkualitas tinggi.',
                'image_path' => 'https://picsum.photos/seed/' . Str::slug($name) . '/400/300',
                'stock' => 1,
                'condition' => 'good',
                'is_active' => true,
            ]);
        }
    }
}
