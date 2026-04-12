<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Barang
                </h2>
                <p class="mt-1 text-sm text-gray-500">Informasi lengkap barang dan atribut pendukungnya.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.items.index') }}" class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                    Kembali
                </a>
                <a href="{{ route('admin.items.edit', $item) }}" class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-sky-700 ring-1 ring-sky-200 hover:bg-sky-50">
                    Edit Barang
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="grid gap-6 md:grid-cols-[220px_1fr]">
                    <div>
                        @if ($item->image_path)
                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="h-52 w-full rounded-xl object-cover ring-1 ring-slate-200" />
                        @else
                            <div class="flex h-52 w-full items-center justify-center rounded-xl bg-slate-100 text-sm text-slate-400 ring-1 ring-slate-200">
                                Tidak ada gambar
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kode Barang</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $item->code }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Barang</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $item->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</p>
                                <p class="mt-1 text-base text-slate-800">{{ $item->category?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kondisi</p>
                                <p class="mt-1 text-base text-slate-800">{{ $conditionLabels[$item->condition] ?? ucfirst($item->condition) }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Deskripsi</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $item->description ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
