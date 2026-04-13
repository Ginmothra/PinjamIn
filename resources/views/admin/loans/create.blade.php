<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Peminjaman
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                @include('admin.loans._form', [
                    'action' => route('admin.loans.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Simpan',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
