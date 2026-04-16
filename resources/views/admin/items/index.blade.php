<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manajemen Barang
                </h2>
                <p class="mt-1 text-sm text-gray-500">Kelola inventaris barang dan kondisi barang yang dapat dipinjam.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.items.export') }}" class="inline-flex items-center rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-700 transition hover:bg-emerald-100">
                    Ekspor Excel
                </a>
                <a href="{{ route('admin.items.template') }}" class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-amber-700 transition hover:bg-amber-100">
                    Unduh Templat
                </a>
                <form method="POST" action="{{ route('admin.items.import') }}" enctype="multipart/form-data" class="flex items-center gap-2 import-form" data-import-form>
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="hidden import-file-input" data-import-file-input />
                    <button type="button" class="inline-flex items-center rounded-md bg-sky-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-sky-700" data-import-trigger>
                        Impor Excel
                    </button>
                </form>
                <a href="{{ route('admin.items.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-black rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest shadow-sm transition ease-in-out duration-150 hover:bg-blue-600 hover:text-white hover:border-black focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Tambah Barang
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if (session('import_errors'))
                <div id="import-errors-data" data-errors="{{ json_encode(session('import_errors')) }}" class="hidden"></div>
            @endif

            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <form method="GET" action="{{ route('admin.items.index') }}" class="grid gap-3 sm:grid-cols-[1fr_220px_auto_auto] sm:items-end">
                    <div>
                        <label for="q" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cari Barang</label>
                        <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kode atau nama barang..." class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500" />
                    </div>

                    <div>
                        <label for="condition" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kondisi</label>
                        <select id="condition" name="condition" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Semua Kondisi</option>
                            @foreach ($conditionOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['condition'] ?? '') === $value)>{{ $label }}</option>
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
                                <th class="px-5 py-3 font-semibold">Kondisi</th>
                                <th class="px-5 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($items as $item)
                                @php
                                    $conditionBadgeMap = [
                                        'good' => 'bg-emerald-600 text-white ring-emerald-600',
                                        'maintenance' => 'bg-orange-500 text-white ring-orange-500',
                                        'damaged' => 'bg-rose-600 text-white ring-rose-600',
                                    ];

                                    $itemCondition = $item->condition;
                                    $itemConditionLabel = $conditionLabels[$itemCondition] ?? ucfirst((string) $itemCondition);
                                    $itemConditionClass = $conditionBadgeMap[$itemCondition] ?? 'bg-slate-200 text-slate-800 ring-slate-300';
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
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $itemConditionClass }}">
                                            {{ $itemConditionLabel }}
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
        // Import Error Modal Handler
        const errorsData = document.getElementById('import-errors-data');
        if (errorsData) {
            const errors = JSON.parse(errorsData.dataset.errors || '[]');
            if (errors.length > 0) {
                showImportErrorsModal(errors);
            }
        }

        function showImportErrorsModal(errors) {
            const errorHtml = errors.map(error =>
                `<div class="mb-2 p-2 bg-red-50 border border-red-200 rounded text-sm">
                    <strong>Baris ${error.row}</strong>: ${error.message}
                </div>`
            ).join('');

            Swal.fire({
                title: 'Detail Error Import',
                html: `<div class="text-left">${errorHtml}</div>`,
                icon: 'warning',
                confirmButtonText: 'Tutup',
                width: 600,
                didOpen: (modal) => {
                    modal.querySelector('.swal2-html-container').style.maxHeight = '400px';
                    modal.querySelector('.swal2-html-container').style.overflowY = 'auto';
                }
            });
        }

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

        document.querySelectorAll('[data-import-form]').forEach((form) => {
            const fileInput = form.querySelector('[data-import-file-input]');
            const triggerButton = form.querySelector('[data-import-trigger]');

            if (!fileInput || !triggerButton) {
                return;
            }

            triggerButton.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', () => {
                if (fileInput.files && fileInput.files.length > 0) {
                    form.submit();
                }
            });
        });
    </script>
</x-app-layout>
