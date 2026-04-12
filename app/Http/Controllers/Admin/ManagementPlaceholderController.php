<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ManagementPlaceholderController extends Controller
{
    public function category(): View
    {
        return $this->render('Kategori', 'CRUD kategori akan dilanjutkan di tahap berikutnya.');
    }

    public function loan(): View
    {
        return $this->render('Peminjaman', 'CRUD peminjaman multi-barang sedang disiapkan.');
    }

    public function loanReturn(): View
    {
        return $this->render('Pengembalian', 'CRUD pengembalian akan dilanjutkan di tahap berikutnya.');
    }

    public function activityLog(): View
    {
        return $this->render('Log Aktifitas', 'Halaman log aktifitas masih placeholder sesuai scope saat ini.');
    }

    private function render(string $module, string $description): View
    {
        return view('admin.management.placeholder', [
            'module' => $module,
            'description' => $description,
        ]);
    }
}
