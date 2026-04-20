<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Reject Peminjaman</h1>
                    <p class="mt-1 text-gray-600">Isi alasan penolakan sebelum memproses peminjaman.</p>
                </div>
                <a href="{{ route('worker.loans.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Kembali
                </a>
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Kode Peminjaman</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $loan->loan_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Peminjam</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $loan->user->name }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('worker.loans.reject', $loan) }}" class="space-y-6 px-6 py-6">
                    @csrf

                    <div>
                        <label for="reject_reason" class="block text-sm font-medium text-gray-700">Reject Reason</label>
                        <textarea id="reject_reason" name="reject_reason" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>{{ old('reject_reason') }}</textarea>
                        @error('reject_reason')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        Peminjaman ini akan ditolak dan status berubah menjadi rejected.
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('worker.loans.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</a>
                        <x-primary-button>Tolak Peminjaman</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>