<!DOCTYPE html>
<html>
<head>
    <title>Laporan Harian PinjamIn</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; text-transform: uppercase; color: #1a56db; }
        .header p { margin: 5px 0; color: #666; }
        
        .section-title { background: #f3f4f6; padding: 8px; font-weight: bold; margin-top: 20px; border-left: 4px solid #1a56db; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; word-wrap: break-word; }
        th { background-color: #f9fafb; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        
        /* Optimasi untuk Halaman Panjang */
        thead { display: table-header-group; } /* Ulangi header di tiap halaman */
        tr { page-break-inside: avoid; } /* Jangan potong baris di tengah halaman */
        
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        
        .footer { margin-top: 50px; }
        .signature-table { width: 100%; border: none; }
        .signature-table td { border: none; text-align: center; width: 50%; }
        .sig-space { height: 80px; }

        .summary-box { margin-top: 20px; display: inline-block; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .summary-item { margin-bottom: 5px; }
        .count { font-weight: bold; color: #1a56db; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Harian - PinjamIn</h1>
        <p>Laporan Aktifitas Peminjaman Alat</p>
        <p>Periode: {{ $date_range }}</p>
    </div>

    <div class="summary-box">
        <strong style="display: block; margin-bottom: 10px;">Ringkasan Aktivitas:</strong>
        <div class="summary-item">Total Peminjaman: <span class="count">{{ $loans->count() }}</span></div>
        <div class="summary-item">Total Pengembalian: <span class="count">{{ $returns->count() }}</span></div>
        <div class="summary-item">Total Barang Rusak: <span class="count">{{ $damaged->count() }}</span></div>
    </div>

    <!-- 1. PEMINJAMAN -->
    <div class="section-title">DAFTAR PEMINJAMAN</div>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Peminjam</th>
                <th>Alat & Qty</th>
                <th>Tgl Pinjam</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $loan)
                <tr>
                    <td>{{ $loan->loan_code }}</td>
                    <td>{{ $loan->user->name }}</td>
                    <td>
                        @foreach($loan->loanItems as $li)
                            {{ $li->quantity }}x {{ $li->item->name }}<br>
                        @endforeach
                    </td>
                    <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($loan->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center">Tidak ada data peminjaman</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. PENGEMBALIAN -->
    <div class="section-title" style="margin-top: 30px;">DAFTAR PENGEMBALIAN</div>
    <table>
        <thead>
            <tr>
                <th>Kode Pinjam</th>
                <th>Peminjam</th>
                <th>Tgl Kembali</th>
                <th>Catatan</th>
                <th>Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returns as $ret)
                <tr>
                    <td>{{ $ret->loan->loan_code }}</td>
                    <td>{{ $ret->loan->user->name }}</td>
                    <td>{{ $ret->return_date->format('d/m/Y') }}</td>
                    <td>{{ $ret->notes ?? '-' }}</td>
                    <td>
                        @if($ret->status == 'completed')
                            <span class="badge badge-success">BAIK</span>
                        @else
                            <span class="badge badge-danger">{{ strtoupper($ret->status) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center">Tidak ada data pengembalian</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. KERUSAKAN -->
    @if($damaged->count() > 0)
        <div class="section-title" style="margin-top: 30px; border-left-color: #dc2626;">DAFTAR BARANG RUSAK / BERMASALAH</div>
        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Peminjam</th>
                    <th>Tgl Lapor</th>
                    <th>Alasan/Catatan Petugas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($damaged as $dmg)
                    <tr>
                        <td>{{ $dmg->item->name }}</td>
                        <td>{{ $dmg->loanReturn->loan->user->name ?? 'Manual Log' }}</td>
                        <td>{{ $dmg->created_at->format('d/m/Y') }}</td>
                        <td>{{ $dmg->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>
                    Dicetak pada: {{ $generated_at }}
                </td>
                <td>
                    Petugas Operasional,
                    <div class="sig-space"></div>
                    <strong>( {{ $worker }} )</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
