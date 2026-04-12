<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manajemen User
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                            'bg-amber-100 text-amber-700' => $user->status === 'pending',
                                            'bg-green-100 text-green-700' => $user->status === 'approved',
                                            'bg-red-100 text-red-700' => $user->status === 'rejected',
                                        ])>
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-sky-700 hover:text-sky-900">Edit</a>

                                            @if ($user->status !== 'approved')
                                                <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-green-700 hover:text-green-900">Approve</button>
                                                </form>
                                            @endif

                                            @if ($user->status !== 'rejected')
                                                <form method="POST" action="{{ route('admin.users.reject', $user) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input
                                                        type="text"
                                                        name="rejection_reason"
                                                        class="rounded-md border-gray-300 text-xs"
                                                        placeholder="Alasan reject"
                                                        required
                                                    />
                                                    <button type="submit" class="text-red-700 hover:text-red-900">Reject</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada data user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
