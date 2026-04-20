<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Data Pengembalian</h1>
                    <p class="mt-1 text-gray-600">Kelola semua pengembalian barang</p>
                </div>
                <a href="{{ route('worker.dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            <!-- Search & Filter -->
            <div class="mb-6 rounded-lg bg-white p-4 shadow-sm">
                <form method="GET" class="flex gap-3">
                    <input type="text" name="search" placeholder="Cari peminjam..." class="flex-1 rounded-md border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Kode Peminjaman</th>
                                <th class="px-5 py-3 font-semibold">Peminjam</th>
                                <th class="px-5 py-3 font-semibold">Tanggal Pengembalian</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($returns as $return)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ $return->loan->loan_code }}</td>
                                    <td class="px-5 py-4">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $return->loan->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $return->loan->user->email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $return->return_date->format('d M Y H:i') }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset
                                            @if($return->status === 'pending')
                                                bg-yellow-100 text-yellow-800 ring-yellow-600
                                            @elseif($return->status === 'completed')
                                                bg-green-100 text-green-800 ring-green-600
                                            @else
                                                bg-gray-100 text-gray-700 ring-gray-600
                                            @endif
                                        ">
                                            {{ ucfirst($return->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('worker.returns.show', $return) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="mt-4 font-medium">Tidak ada data pengembalian</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($returns->hasPages())
                <div class="mt-6">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
