<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Detail Pengembalian</h1>
                <a href="{{ route('worker.returns.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            @if($loanReturn->status === 'partial')
                <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 border-l-4 border-amber-500">
                    <div class="flex items-center justify-between mb-3 px-2">
                        <span class="text-sm font-bold text-slate-700 uppercase tracking-wider">Aksi Validasi</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('worker.returns.accept', $loanReturn) }}" onsubmit="return confirm('Terima pengembalian ini? Stok barang akan otomatis bertambah.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Terima & Selesai
                            </button>
                        </form>

                        <a href="{{ route('worker.returns.reject-form', $loanReturn) }}" class="inline-flex items-center rounded-md bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Tolak (Minta Foto Ulang)
                        </a>

                        <a href="{{ route('worker.returns.damaged', $loanReturn) }}" class="inline-flex items-center rounded-md bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 transition">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Ada Barang Rusak
                        </a>
                    </div>
                </div>
            @endif

            @php
                $returnStatusBadges = [
                    'partial' => 'bg-yellow-100 text-yellow-800 ring-yellow-600',
                    'completed' => 'bg-green-100 text-green-800 ring-green-600',
                    'rejected' => 'bg-red-100 text-red-800 ring-red-600',
                ];

                $returnStatusLabels = [
                    'partial' => 'Pending',
                    'completed' => 'Selesai',
                    'rejected' => 'Ditolak',
                ];

                $itemConditionBadges = [
                    'good' => 'bg-green-100 text-green-800',
                    'maintenance' => 'bg-yellow-100 text-yellow-800',
                    'damaged' => 'bg-red-100 text-red-800',
                ];

                $itemConditionLabels = [
                    'good' => 'Baik',
                    'maintenance' => 'Perbaikan',
                    'damaged' => 'Rusak',
                ];
            @endphp

            <!-- Info Card -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-slate-200 mb-6">
                <div class="px-6 py-8">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Kode Peminjaman</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $loanReturn->loan->loan_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset {{ $returnStatusBadges[$loanReturn->status] ?? 'bg-gray-100 text-gray-700 ring-gray-600' }}">
                                    {{ $returnStatusLabels[$loanReturn->status] ?? ucfirst($loanReturn->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tanggal Pengembalian</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $loanReturn->return_date->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Dibuat pada</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $loanReturn->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Peminjam Info -->
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Informasi Peminjam</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Nama</p>
                                <p class="mt-1 text-gray-900">{{ $loanReturn->loan->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email</p>
                                <p class="mt-1 text-gray-900">{{ $loanReturn->loan->user->email }}</p>
                            </div>
                            @if($loanReturn->loan->user->kelas)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Kelas</p>
                                    <p class="mt-1 text-gray-900">{{ $loanReturn->loan->user->kelas }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Proof Image -->
                    @if($loanReturn->proof_image)
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Foto Bukti Pengembalian</h3>
                            <div class="max-w-md overflow-hidden rounded-xl border border-slate-200 group relative">
                                <a href="{{ $loanReturn->proof_image }}" target="_blank" title="Klik untuk memperbesar">
                                    <img src="{{ $loanReturn->proof_image }}" alt="Bukti Foto" class="h-auto w-full object-contain transition group-hover:opacity-90">
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition bg-black/20 text-white font-bold text-sm">
                                        Klik untuk Zoom
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($loanReturn->notes)
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Catatan Pengembalian</h3>
                            <p class="text-gray-600">{{ $loanReturn->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Barang Dikembalikan -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                <div class="px-6 py-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Barang Dikembalikan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Nama Barang</th>
                                <th class="px-6 py-3 font-semibold">Kategori</th>
                                <th class="px-6 py-3 font-semibold">Jumlah</th>
                                <th class="px-6 py-3 font-semibold">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($loanReturn->loan->loanItems as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item->item->name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $item->item->category->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $itemConditionBadges[$item->item->condition] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $itemConditionLabels[$item->item->condition] ?? ucfirst($item->item->condition) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
