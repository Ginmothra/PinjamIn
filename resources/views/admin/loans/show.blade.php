<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Peminjaman</h2>
                <p class="mt-1 text-sm text-gray-500">Informasi lengkap transaksi peminjaman barang.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.loans.index') }}" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Kembali</a>
                <a href="{{ route('admin.loans.edit', $loan) }}" class="inline-flex items-center rounded-md bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-4 px-4 sm:px-6 lg:px-8">
            @php
                $statusClass = $statusBadges[$loan->status] ?? 'bg-slate-200 text-slate-800 ring-slate-300';
            @endphp

            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kode Peminjaman</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $loan->loan_code }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                        <span class="mt-1 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">
                            {{ $statusLabels[$loan->status] ?? ucfirst($loan->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peminjam</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $loan->user?->name ?? '-' }}</p>
                        <p class="text-sm text-slate-500">{{ $loan->user?->email ?? '-' }}</p>
                        @if ($loan->user?->kelas)
                            <p class="text-xs text-slate-500">Kelas: {{ $loan->user->kelas }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Disetujui Oleh</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $loan->approver?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Pinjam</p>
                        <p class="mt-1 text-base text-slate-900">{{ $loan->loan_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Pengembalian</p>
                        <p class="mt-1 text-base text-slate-900">{{ $loan->due_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alasan Peminjaman</p>
                        <p class="mt-1 rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $loan->notes ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Kode Barang</th>
                                <th class="px-5 py-3 font-semibold">Nama Barang</th>
                                <th class="px-5 py-3 font-semibold">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($loan->loanItems as $loanItem)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-900">{{ $loanItem->item?->code ?? '-' }}</td>
                                    <td class="px-5 py-4 text-slate-700">{{ $loanItem->item?->name ?? '-' }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $loanItem->notes ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-slate-500">Belum ada detail barang pada peminjaman ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
