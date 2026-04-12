<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manajemen Kategori
                </h2>
                <p class="mt-1 text-sm text-gray-500">Kelola kategori barang yang tersedia di sistem.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center px-4 py-2 bg-[linear-gradient(120deg,#20a7db_0%,#007dff_62%,#ffffff_130%)] border border-[#007dff]/35 rounded-md font-semibold text-xs text-[#00356d] uppercase tracking-widest shadow-[0_8px_20px_rgba(0,125,255,0.20)] hover:shadow-[0_10px_24px_rgba(0,125,255,0.26)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Tambah Kategori
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Deskripsi</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-900">{{ $category->name }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ Str::limit($category->description, 50) ?? '-' }}</td>
                                    <td class="px-5 py-4">
                                        @if ($category->is_active)
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-emerald-600 text-white ring-1 ring-inset ring-emerald-600">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-400 text-white ring-1 ring-inset ring-slate-400">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-sky-700 transition hover:bg-sky-50 hover:text-sky-900">Edit</a>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="delete-category-form inline-flex" data-category-name="{{ $category->name }}">
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
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada data kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-3 rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">
                    Menampilkan {{ $categories->firstItem() ?? 0 }}-{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori.
                </p>
                <div>
                    {{ $categories->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('.delete-category-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const categoryName = form.dataset.categoryName || 'kategori ini';

                Swal.fire({
                    title: 'Hapus kategori?',
                    text: 'Data ' + categoryName + ' akan dihapus permanen.',
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
