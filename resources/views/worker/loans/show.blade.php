<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Detail Peminjaman</h1>
                <a href="{{ route('worker.loans.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            @php
                $statusLabels = [
                    'pending' => 'Pending',
                    'borrowed' => 'Dipinjam',
                    'returned' => 'Dikembalikan',
                    'completed' => 'Selesai',
                ];

                $statusBadges = [
                    'pending' => 'bg-orange-500 text-white ring-orange-500',
                    'borrowed' => 'bg-rose-600 text-white ring-rose-600',
                    'returned' => 'bg-sky-600 text-white ring-sky-600',
                    'completed' => 'bg-emerald-600 text-white ring-emerald-600',
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

                $returnStatusBadges = [
                    'partial' => 'bg-yellow-100 text-yellow-800',
                    'completed' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                ];

                $returnStatusLabels = [
                    'partial' => 'Pending',
                    'completed' => 'Selesai',
                    'rejected' => 'Ditolak',
                ];
            @endphp

            <!-- Info Card -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-slate-200 mb-6">
                <div class="px-6 py-8">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Kode Peminjaman</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $loan->loan_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset {{ $statusBadges[$loan->status] ?? 'bg-gray-100 text-gray-700 ring-gray-600' }}">
                                    {{ $statusLabels[$loan->status] ?? $loan->status }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tanggal Peminjaman</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $loan->loan_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tenggat Kembali</p>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $loan->due_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    <!-- Peminjam Info -->
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Informasi Peminjam</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Nama</p>
                                <p class="mt-1 text-gray-900">{{ $loan->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email</p>
                                <p class="mt-1 text-gray-900">{{ $loan->user->email }}</p>
                            </div>
                            @if($loan->user->kelas)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Kelas</p>
                                    <p class="mt-1 text-gray-900">{{ $loan->user->kelas }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($loan->notes)
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Catatan</h3>
                            <p class="text-gray-600">{{ $loan->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Barang Dipinjam -->
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-slate-200 mb-6">
                <div class="px-6 py-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Barang Dipinjam</h2>
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
                            @foreach($loan->loanItems as $item)
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

            <!-- Pengembalian Info -->
            @if($loan->loanReturns->isNotEmpty())
                <div class="rounded-lg bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="px-6 py-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Riwayat Pengembalian</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-slate-600">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Tanggal Pengembalian</th>
                                    <th class="px-6 py-3 font-semibold">Status</th>
                                    <th class="px-6 py-3 font-semibold">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($loan->loanReturns as $return)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-gray-900">{{ $return->return_date->format('d M Y H:i') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $returnStatusBadges[$return->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $returnStatusLabels[$return->status] ?? ucfirst($return->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">{{ $return->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
