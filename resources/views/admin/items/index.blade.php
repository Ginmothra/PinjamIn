<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manajemen Barang
                </h2>
                <p class="mt-1 text-sm text-gray-500">Kelola inventaris barang yang dapat dipinjam.</p>
            </div>
            <a href="{{ route('admin.items.create') }}" class="inline-flex items-center px-4 py-2 bg-[linear-gradient(120deg,#20a7db_0%,#007dff_62%,#ffffff_130%)] border border-[#007dff]/35 rounded-md font-semibold text-xs text-[#00356d] uppercase tracking-widest shadow-[0_8px_20px_rgba(0,125,255,0.20)] hover:shadow-[0_10px_24px_rgba(0,125,255,0.26)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Tambah Barang
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <form method="GET" action="{{ route('admin.items.index') }}" class="grid gap-3 sm:grid-cols-[1fr_220px_auto_auto] sm:items-end">
                    <div>
                        <label for="q" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cari Barang</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kode atau nama barang..." class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500" />
                    </div>

                    <div>
                        <label for="status" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Semua Status</option>
                            @foreach ($itemStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                        Terapkan
                    </button>

                    <a href="{{ route('admin.items.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Reset
                    </a>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Gambar</th>
                                <th class="px-5 py-3 font-semibold">Kode</th>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($items as $item)
                                @php
                                    $loanStatuses = $item->loanItems->pluck('loan.status')->filter();
                                    $itemStatus = 'aktif';

                                    if ($loanStatuses->contains('borrowed')) {
                                        $itemStatus = 'dipinjam';
                                    } elseif ($loanStatuses->contains('returned')) {
                                        $itemStatus = 'dikembalikan';
                                    }

                                    $itemStatusClass = $itemStatusBadges[$itemStatus] ?? 'bg-slate-200 text-slate-800 ring-slate-300';
                                @endphp
                                <tr class="item-row cursor-pointer hover:bg-sky-50/40" data-detail-url="{{ route('admin.items.show', $item) }}" tabindex="0" role="link" aria-label="Lihat detail barang {{ $item->name }}">
                                    <td class="px-5 py-4">
                                        @if ($item->image_path)
                                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="h-14 w-14 rounded-lg object-cover ring-1 ring-slate-200" />
                                        @else
                                            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400 ring-1 ring-slate-200">
                                                Tidak ada
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-medium text-slate-900">{{ $item->code }}</td>
                                    <td class="px-5 py-4">
                                        <span class="font-medium text-slate-900">{{ $item->name }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $itemStatusClass }}">
                                            {{ ucfirst($itemStatus) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 no-row-click">
                                        <div class="flex items-center gap-2 no-row-click">
                                            <a href="{{ route('admin.items.edit', $item) }}" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-sky-700 transition hover:bg-sky-50 hover:text-sky-900">Edit</a>
                                            <form method="POST" action="{{ route('admin.items.destroy', $item) }}" class="delete-item-form inline-flex no-row-click" data-item-name="{{ $item->name }}">
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
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada data barang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-3 rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">
                    Menampilkan {{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} barang.
                </p>
                <div>
                    {{ $items->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.item-row').forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('.no-row-click, a, button, form')) {
                    return;
                }

                const detailUrl = row.dataset.detailUrl;
                if (detailUrl) {
                    window.location.href = detailUrl;
                }
            });

            row.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                if (event.target.closest('.no-row-click, a, button, form')) {
                    return;
                }

                event.preventDefault();
                const detailUrl = row.dataset.detailUrl;
                if (detailUrl) {
                    window.location.href = detailUrl;
                }
            });
        });

        document.querySelectorAll('.delete-item-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const itemName = form.dataset.itemName || 'barang ini';

                Swal.fire({
                    title: 'Hapus barang?',
                    text: 'Data ' + itemName + ' akan dihapus permanen.',
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
