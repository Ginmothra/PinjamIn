<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Barang
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('admin.items.update', $item) }}" class="space-y-5" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="category_id" value="Kategori" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                    </div>

                    <div>
                        <x-input-label for="code" value="Kode Barang" />
                        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $item->code)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('code')" />
                    </div>

                    <div>
                        <x-input-label for="name" value="Nama Barang" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $item->name)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="condition" value="Kondisi" />
                        <select id="condition" name="condition" class="mt-1 block w-full rounded-md border-gray-300" required>
                            @foreach ($conditionLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('condition', $item->condition) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('condition')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Deskripsi" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $item->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div>
                        <x-input-label for="image" value="Gambar Barang" />
                        @if ($item->image_path)
                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}" class="mb-3 h-24 w-24 rounded-lg object-cover ring-1 ring-slate-200" />
                        @endif
                        <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-sky-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-sky-700 hover:file:bg-sky-100" />
                        <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengganti gambar.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                    </div>

                    <div class="rounded-md bg-sky-50 px-3 py-2 text-sm text-sky-800">
                        Stok barang otomatis diset ke 1.
                    </div>

                    <div>
                        <label for="is_active" class="flex items-center gap-2 cursor-pointer">
                            <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $item->is_active)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <span class="text-sm text-gray-700">Barang aktif</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.items.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Batal</a>
                        <x-primary-button>Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
