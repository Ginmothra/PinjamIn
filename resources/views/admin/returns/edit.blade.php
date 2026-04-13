<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Pengembalian
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                @include('admin.returns._form', [
                    'action' => route('admin.returns.update', $loanReturn),
                    'method' => 'PUT',
                    'submitLabel' => 'Simpan Perubahan',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
