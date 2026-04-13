@php
    $loanItemsInput = old('items');

    if (! is_array($loanItemsInput)) {
        $loanItemsInput = isset($loan)
            ? $loan->loanItems->map(static fn ($loanItem) => [
                'item_id' => $loanItem->item_id,
                'notes' => $loanItem->notes,
            ])->values()->all()
            : [['item_id' => '', 'notes' => '']];
    }
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <x-input-label for="loan_code" value="Kode Peminjaman" />
            <x-text-input id="loan_code" name="loan_code" type="text" class="mt-1 block w-full" :value="old('loan_code', $loan->loan_code ?? ($generatedLoanCode ?? ''))" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('loan_code')" />
        </div>

        <div>
            <x-input-label for="user_id" value="Peminjam" />
            <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300" required>
                <option value="">Pilih user</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id', $loan->user_id ?? '') === (string) $user->id)>
                        {{ $user->name }} - {{ $user->email }}{{ $user->kelas ? ' (' . $user->kelas . ')' : '' }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('user_id')" />
        </div>

        <div>
            <x-input-label for="loan_date" value="Tanggal Peminjaman" />
            <x-text-input id="loan_date" name="loan_date" type="date" class="mt-1 block w-full" :value="old('loan_date', isset($loan) ? $loan->loan_date?->format('Y-m-d') : now()->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('loan_date')" />
        </div>

        <div>
            <x-input-label for="due_date" value="Tanggal Pengembalian" />
            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', isset($loan) ? $loan->due_date?->format('Y-m-d') : now()->addDays(7)->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300" required>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $loan->status ?? 'pending') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('status')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="notes" value="Alasan Peminjaman" />
            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $loan->notes ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800">Detail Barang Dipinjam</h3>
            <button type="button" id="add-loan-item" class="inline-flex items-center rounded-md bg-sky-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-sky-700">
                Tambah Baris Barang
            </button>
        </div>

        <p class="mb-3 text-xs text-slate-500">Barang dengan kondisi rusak atau sedang diperbaiki, serta barang yang sedang ada di peminjaman berstatus pending atau dipinjam, tidak akan ditampilkan.</p>

        <x-input-error class="mb-3" :messages="$errors->get('items')" />

        <div id="loan-items-wrapper" class="space-y-3">
            @foreach ($loanItemsInput as $index => $loanItemInput)
                <div class="loan-item-row rounded-md border border-slate-200 p-3">
                    <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto] md:items-start">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Barang</label>
                            <select name="items[{{ $index }}][item_id]" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                                <option value="">Pilih barang</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}" @selected((string) ($loanItemInput['item_id'] ?? '') === (string) $item->id)>
                                        {{ $item->code }} - {{ $item->name }}{{ $item->category ? ' (' . $item->category->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('items.' . $index . '.item_id')" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan Barang</label>
                            <input name="items[{{ $index }}][notes]" type="text" value="{{ $loanItemInput['notes'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm" placeholder="Opsional" />
                            <x-input-error class="mt-2" :messages="$errors->get('items.' . $index . '.notes')" />
                        </div>

                        <div class="pt-5 md:pt-6">
                            <button type="button" class="remove-loan-item inline-flex items-center rounded-md px-3 py-2 text-xs font-semibold uppercase tracking-wide text-rose-700 transition hover:bg-rose-50">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.loans.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">Batal</a>
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
    </div>
</form>

<template id="loan-item-template">
    <div class="loan-item-row rounded-md border border-slate-200 p-3">
        <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto] md:items-start">
            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Barang</label>
                <select name="items[__INDEX__][item_id]" class="mt-1 block w-full rounded-md border-gray-300 text-sm" required>
                    <option value="">Pilih barang</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->name }}{{ $item->category ? ' (' . $item->category->name . ')' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan Barang</label>
                <input name="items[__INDEX__][notes]" type="text" value="" class="mt-1 block w-full rounded-md border-gray-300 text-sm" placeholder="Opsional" />
            </div>

            <div class="pt-5 md:pt-6">
                <button type="button" class="remove-loan-item inline-flex items-center rounded-md px-3 py-2 text-xs font-semibold uppercase tracking-wide text-rose-700 transition hover:bg-rose-50">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    (() => {
        const wrapper = document.getElementById('loan-items-wrapper');
        const template = document.getElementById('loan-item-template');
        const addButton = document.getElementById('add-loan-item');

        if (!wrapper || !template || !addButton) {
            return;
        }

        const getNextIndex = () => wrapper.querySelectorAll('.loan-item-row').length;

        const syncRemoveButtons = () => {
            const rows = wrapper.querySelectorAll('.loan-item-row');
            rows.forEach((row) => {
                const removeButton = row.querySelector('.remove-loan-item');
                if (!removeButton) {
                    return;
                }

                removeButton.disabled = rows.length <= 1;
                removeButton.classList.toggle('opacity-40', rows.length <= 1);
                removeButton.classList.toggle('cursor-not-allowed', rows.length <= 1);
            });
        };

        const appendRow = () => {
            const html = template.innerHTML.replaceAll('__INDEX__', getNextIndex().toString());
            wrapper.insertAdjacentHTML('beforeend', html);
            syncRemoveButtons();
        };

        addButton.addEventListener('click', appendRow);

        wrapper.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-loan-item');
            if (!removeButton) {
                return;
            }

            const row = removeButton.closest('.loan-item-row');
            if (!row || wrapper.querySelectorAll('.loan-item-row').length <= 1) {
                return;
            }

            row.remove();
            syncRemoveButtons();
        });

        syncRemoveButtons();
    })();
</script>
