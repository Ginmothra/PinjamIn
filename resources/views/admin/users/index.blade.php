<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manajemen User
                </h2>
                <p class="mt-1 text-sm text-gray-500">Kelola akun, role, dan akses pengguna dari satu tempat.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.users.export') }}" class="inline-flex items-center rounded-md border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-emerald-700 transition hover:bg-emerald-100">
                    Ekspor Excel
                </a>
                <a href="{{ route('admin.users.template') }}" class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-amber-700 transition hover:bg-amber-100">
                    Unduh Templat
                </a>
                <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="flex items-center gap-2 import-form" data-import-form>
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="hidden import-file-input" data-import-file-input />
                    <button type="button" class="inline-flex items-center rounded-md bg-sky-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-sky-700" data-import-trigger>
                        Impor Excel
                    </button>
                </form>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-black rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest shadow-sm transition ease-in-out duration-150 hover:bg-blue-600 hover:text-white hover:border-black focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Tambah User
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-4" :status="session('status')" />
            @if (session('import_errors'))
                <div id="import-errors-data" data-errors="{{ json_encode(session('import_errors')) }}" class="hidden"></div>
            @endif
            @php
                $roleBadgeClasses = [
                    'admin' => 'bg-emerald-600 text-white ring-emerald-600',
                    'worker' => 'bg-rose-600 text-white ring-rose-600',
                    'user' => 'bg-sky-600 text-white ring-sky-600',
                    'technician' => 'bg-amber-500 text-slate-900 ring-amber-500',
                ];
            @endphp
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-600">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Email</th>
                                <th class="px-5 py-3 font-semibold">Kelas</th>
                                <th class="px-5 py-3 font-semibold">Role</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-900">{{ $user->name }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $user->email }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $user->kelas ?? '-' }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @forelse ($user->roles as $role)
                                                @php
                                                    $roleClasses = $roleBadgeClasses[$role->name] ?? 'bg-slate-200 text-slate-800 ring-slate-300';
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $roleClasses }}">
                                                    {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                                                </span>
                                            @empty
                                                <span class="text-slate-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($user->is_active)
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-emerald-100 text-emerald-800 ring-emerald-300">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-slate-100 text-slate-800 ring-slate-300">
                                                Tidak Aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-sky-700 transition hover:bg-sky-50 hover:text-sky-900">Edit</a>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="delete-user-form inline-flex" data-user-name="{{ $user->name }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50 hover:text-rose-900">
                                                    Hapus
                                                </button>
                                            </form>
                                            @if (!$user->is_active)
                                                <form method="POST" action="{{ route('admin.users.resend-activation', $user) }}" class="inline-flex">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-amber-700 transition hover:bg-amber-50 hover:text-amber-900">
                                                        Kirim Ulang Email
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada data user.</td>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Import Error Modal Handler
        const errorsData = document.getElementById('import-errors-data');
        if (errorsData) {
            const errors = JSON.parse(errorsData.dataset.errors || '[]');
            if (errors.length > 0) {
                showImportErrorsModal(errors);
            }
        }

        function showImportErrorsModal(errors) {
            const errorHtml = errors.map(error =>
                `<div class="mb-2 p-2 bg-red-50 border border-red-200 rounded text-sm">
                    <strong>Baris ${error.row}</strong>: ${error.message}
                </div>`
            ).join('');

            Swal.fire({
                title: 'Detail Error Import',
                html: `<div class="text-left">${errorHtml}</div>`,
                icon: 'warning',
                confirmButtonText: 'Tutup',
                width: 600,
                didOpen: (modal) => {
                    modal.querySelector('.swal2-html-container').style.maxHeight = '400px';
                    modal.querySelector('.swal2-html-container').style.overflowY = 'auto';
                }
            });
        }

        document.querySelectorAll('.delete-user-form').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();

                const userName = form.dataset.userName || 'user ini';

                Swal.fire({
                    title: 'Hapus user?',
                    text: 'Data ' + userName + ' akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('[data-import-form]').forEach((form) => {
            const fileInput = form.querySelector('[data-import-file-input]');
            const triggerButton = form.querySelector('[data-import-trigger]');

            if (!fileInput || !triggerButton) {
                return;
            }

            triggerButton.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', () => {
                if (fileInput.files && fileInput.files.length > 0) {
                    form.submit();
                }
            });
        });
    </script>
</x-app-layout>
