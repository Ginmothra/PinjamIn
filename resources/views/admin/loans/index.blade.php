<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manajemen Peminjaman
                </h2>
                <p class="mt-1 text-sm text-gray-500">Kelola transaksi peminjaman barang beserta statusnya.</p>
            </div>
            <a href="{{ route('admin.loans.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-black rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest shadow-sm transition ease-in-out duration-150 hover:bg-blue-600 hover:text-white hover:border-black focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Tambah Peminjaman
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto space-y-4 sm:px-6 lg:px-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <form method="GET" action="{{ route('admin.loans.index') }}" class="grid gap-3 sm:grid-cols-[1fr_220px_auto_auto] sm:items-end">
                    <div>
                        <label for="q" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cari Peminjaman</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kode, nama user, email, atau kelas..." class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500" />
                    </div>

                    <div>
                        <label for="status" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Semua Status</option>
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                        Terapkan
                    </button>

                    <a href="{{ route('admin.loans.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Reset
                    </a>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Kode</th>
                                <th class="px-5 py-3 font-semibold">Peminjam</th>
                                <th class="px-5 py-3 font-semibold">Tanggal</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Total Item</th>
                                <th class="px-5 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($loans as $loan)
                                @php
                                    $statusClass = $statusBadges[$loan->status] ?? 'bg-slate-200 text-slate-800 ring-slate-300';
                                @endphp
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-900">{{ $loan->loan_code }}</td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-900">{{ $loan->user?->name ?? '-' }}</p>
                                        <p class="text-xs text-slate-500">{{ $loan->user?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        <p>Pinjam: {{ $loan->loan_date?->format('d M Y') ?? '-' }}</p>
                                        <p class="text-xs">Pengembalian: {{ $loan->due_date?->format('d M Y') ?? '-' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">
                                            {{ $statusLabels[$loan->status] ?? ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ (int) ($loan->loan_items_sum_quantity ?? 0) }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.loans.show', $loan) }}" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Detail</a>
                                            <a href="{{ route('admin.loans.edit', $loan) }}" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-sky-700 transition hover:bg-sky-50 hover:text-sky-900">Edit</a>
                                            <form method="POST" action="{{ route('admin.loans.destroy', $loan) }}" class="delete-loan-form inline-flex" data-loan-code="{{ $loan->loan_code }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50 hover:text-rose-900">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada data peminjaman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-3 rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">
                    Menampilkan {{ $loans->firstItem() ?? 0 }}-{{ $loans->lastItem() ?? 0 }} dari {{ $loans->total() }} peminjaman.
                </p>
                <div>
                    {{ $loans->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.delete-loan-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const loanCode = form.dataset.loanCode || 'peminjaman ini';

                Swal.fire({
                    title: 'Hapus peminjaman?',
                    text: 'Data ' + loanCode + ' akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</x-app-layout>
