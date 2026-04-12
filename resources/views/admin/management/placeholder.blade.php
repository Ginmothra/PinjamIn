<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $module }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900">Modul {{ $module }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $description }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
