<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white shadow-md rounded-lg p-5">
                    <p class="text-sm text-gray-500">Peminjaman Berlangsung</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['ongoing_loans'] }}</p>
                </div>
                <div class="bg-white shadow-md rounded-lg p-5">
                    <p class="text-sm text-gray-500">Peminjaman Pending</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['pending_loans'] }}</p>
                </div>
                <div class="bg-white shadow-md rounded-lg p-5">
                    <p class="text-sm text-gray-500">Total Pengembalian</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['returns_total'] }}</p>
                </div>
                <div class="bg-white shadow-md rounded-lg p-5">
                    <p class="text-sm text-gray-500">Total Barang</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total_items'] }}</p>
                </div>
                <div class="bg-white shadow-md rounded-lg p-5">
                    <p class="text-sm text-gray-500">Total Kategori</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total_categories'] }}</p>
                </div>
                <div class="bg-white shadow-md rounded-lg p-5">
                    <p class="text-sm text-gray-500">Total User</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total_users'] }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-5">
                <h3 class="text-lg font-semibold text-gray-900">Ringkasan Status Peminjaman</h3>

                <div class="mt-4 divide-y divide-gray-100">
                    @forelse ($loanStatusCounts as $status => $total)
                        <div class="py-2 flex items-center justify-between text-sm">
                            <span class="capitalize text-gray-700">{{ $status }}</span>
                            <span class="font-medium text-gray-900">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="py-2 text-sm text-gray-500">Belum ada data peminjaman.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
