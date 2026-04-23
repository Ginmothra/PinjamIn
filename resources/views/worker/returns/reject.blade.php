<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Tolak Pengembalian</h1>
                <p class="text-sm text-gray-600">Berikan alasan kenapa pengembalian alat ini ditolak</p>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-6 rounded-lg bg-slate-50 p-4">
                    <h3 class="text-sm font-semibold text-slate-700 mb-2">Detail Peminjaman:</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Kode:</p>
                            <p class="font-medium text-slate-900">{{ $loanReturn->loan->loan_code }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Peminjam:</p>
                            <p class="font-medium text-slate-900">{{ $loanReturn->loan->user->name }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('worker.returns.reject', $loanReturn) }}">
                    @csrf
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <textarea 
                            name="notes" 
                            id="notes" 
                            rows="4" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Contoh: Foto bukti buram atau barang tidak lengkap..."
                            required
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('worker.returns.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                        <button type="submit" class="inline-flex items-center rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                            Konfirmasi Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
