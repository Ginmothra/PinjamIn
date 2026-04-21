<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Denda</h1>
                    <p class="mt-2 text-slate-600 font-medium">Pantau dan kelola denda keterlambatan atau kerusakan barang.</p>
                </div>
            </div>

            <!-- Search -->
            <div class="mb-6">
                <form method="GET" action="{{ route('admin.fines.index') }}" class="flex gap-4">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email user..." class="w-full rounded-2xl border-slate-200 bg-white px-5 py-3 pl-12 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="absolute left-4 top-3.5 text-slate-400">
                            🔍
                        </div>
                    </div>
                    <button type="submit" class="rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                        Cari User
                    </button>
                </form>
            </div>

            @if(session('status'))
                <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-100">
                    <p class="text-sm font-bold text-emerald-800">{{ session('status') }}</p>
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Total Denda (Belum Lunas)</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Kasus</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-slate-900">{{ $user->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-extrabold {{ $user->unpaid_fines_sum > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        Rp {{ number_format($user->unpaid_fines_sum ?? 0, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                        {{ $user->unpaid_fines_count ?? 0 }} denda
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.fines.show', $user) }}" class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-bold text-indigo-600 shadow-sm ring-1 ring-indigo-200 hover:bg-indigo-50 transition-all">
                                        Detail & Tambah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic font-medium">
                                    Tidak ada data user ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
