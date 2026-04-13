<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    @if (isset($loanReturn))
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peminjaman Terkait</p>
            <p class="mt-1 font-semibold text-slate-900">{{ $loanReturn->loan?->loan_code ?? '-' }}</p>
            <p class="text-sm text-slate-600">{{ $loanReturn->loan?->user?->name ?? '-' }} - {{ $loanReturn->loan?->user?->email ?? '-' }}</p>
        </div>
        <input type="hidden" name="loan_id" value="{{ $loanReturn->loan_id }}" />
    @else
        <div>
            <x-input-label for="loan_id" value="Pilih Peminjaman" />
            <select id="loan_id" name="loan_id" class="mt-1 block w-full rounded-md border-gray-300" required @disabled($returnableLoans->isEmpty())>
                <option value="">Pilih peminjaman</option>
                @foreach ($returnableLoans as $loan)
                    <option value="{{ $loan->id }}" @selected((string) old('loan_id') === (string) $loan->id)>
                        {{ $loan->loan_code }} - {{ $loan->user?->name ?? '-' }}{{ $loan->user?->kelas ? ' (' . $loan->user->kelas . ')' : '' }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('loan_id')" />
            @if ($returnableLoans->isEmpty())
                <p class="mt-2 text-xs text-amber-700">Belum ada peminjaman berstatus dipinjam yang bisa diproses pengembaliannya.</p>
            @endif
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="return_date" value="Tanggal Pengembalian" />
            <x-text-input id="return_date" name="return_date" type="date" class="mt-1 block w-full" :value="old('return_date', isset($loanReturn) ? $loanReturn->return_date?->format('Y-m-d') : now()->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('return_date')" />
        </div>

        <div>
            <x-input-label for="status" value="Status Pengembalian" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300" required>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $loanReturn->status ?? 'partial') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('status')" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Catatan Pengembalian" />
        <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $loanReturn->notes ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>

    <div class="rounded-md bg-sky-50 px-3 py-2 text-xs text-sky-800">
        Status "Selesai" akan mengubah status peminjaman menjadi "Dikembalikan" dan membuka barang untuk dipinjam lagi.
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.returns.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Batal</a>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>
