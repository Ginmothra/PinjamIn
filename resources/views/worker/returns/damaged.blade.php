<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Laporkan Barang Rusak</h1>
                    <p class="mt-1 text-gray-600">Jelaskan kerusakan barang yang ditemukan saat pengembalian.</p>
                </div>
                <a href="{{ route('worker.returns.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Kembali
                </a>
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Kode Peminjaman</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $return->loan->loan_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Peminjam</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $return->loan->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Daftar Alat</p>
                            <ul class="list-inside list-disc text-slate-900 font-medium">
                                @foreach($return->loan->loanItems as $li)
                                    <li>{{ $li->item->name }} ({{ $li->quantity }}x)</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('worker.returns.mark-damaged', $return) }}" class="space-y-6 px-6 py-6">
                    @csrf

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi Kerusakan</label>
                        <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Contoh: Kamera lensa pecah, kabel power putus, dll..." required>{{ old('description') }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">Informasi ini akan diteruskan ke tim Teknisi untuk proses perbaikan.</p>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Status barang akan berubah menjadi <strong>Damaged</strong> dan pengembalian akan ditandai selesai dengan catatan kerusakan.
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('worker.returns.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</a>
                        <button type="submit" class="inline-flex items-center rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">
                            Laporkan Kerusakan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
