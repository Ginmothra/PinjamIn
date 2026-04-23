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

            <x-auth-session-status class="mb-4" :status="session('status')" />
            
            @if(session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 ring-1 ring-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

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
                        <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Barang yang Rusak <span class="text-rose-500">*</span></label>
                        <div class="space-y-3 rounded-lg border border-slate-200 p-4">
                            @foreach($return->loan->loanItems as $li)
                                <label class="flex items-center gap-3 p-2 rounded-md hover:bg-slate-50 cursor-pointer transition">
                                    <input type="checkbox" name="damaged_items[]" value="{{ $li->item_id }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-900">{{ $li->item->name }}</p>
                                        <p class="text-xs text-slate-500">Kode: {{ $li->item->code }} | Jumlah: {{ $li->quantity }}x</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('damaged_items')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

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
