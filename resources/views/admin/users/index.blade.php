<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manajemen User
                </h2>
                <p class="mt-1 text-sm text-gray-500">Kelola akun, role, dan akses pengguna dari satu tempat.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-[linear-gradient(120deg,#20a7db_0%,#007dff_62%,#ffffff_130%)] border border-[#007dff]/35 rounded-md font-semibold text-xs text-[#00356d] uppercase tracking-widest shadow-[0_8px_20px_rgba(0,125,255,0.20)] hover:shadow-[0_10px_24px_rgba(0,125,255,0.26)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Tambah User
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-4" :status="session('status')" />
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Email</th>
                                <th class="px-5 py-3 font-semibold">Role</th>
                                <th class="px-5 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-900">{{ $user->name }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $user->email }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @forelse ($user->roles as $role)
                                                @php
                                                    $roleClasses = $roleBadges[$role->name] ?? 'bg-slate-200 text-slate-800 ring-slate-300';
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $roleClasses }}">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @empty
                                                <span class="text-slate-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-sky-700 transition hover:bg-sky-50 hover:text-sky-900">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada data user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-3 rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">
                    Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} user.
                </p>
                <div>
                    {{ $users->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
