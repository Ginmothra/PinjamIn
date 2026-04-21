<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManagementPlaceholderController extends Controller
{
    // ... method lain tetap ada atau diabaikan jika sudah ada controller asli

    public function activityLog(Request $request): View
    {
        $query = DB::table('activity_logs');

        // Filter Tipe Log
        if ($request->filled('type')) {
            $query->where('log_type', $request->type);
        }

        // Filter Aksi
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.management.activity-log', [
            'logs' => $logs,
            'types' => ['USERS', 'ITEMS', 'LOANS', 'RETURNS', 'REPAIRS', 'FINES'],
            'actions' => ['INSERT', 'UPDATE', 'DELETE', 'STATUS_CHANGE']
        ]);
    }

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

    private function render(string $module, string $description): View
    {
        return view('admin.management.placeholder', [
            'module' => $module,
            'description' => $description,
        ]);
    }
}
