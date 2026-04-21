<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TemplateExport;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserActivationMail;
use Illuminate\Auth\Passwords\PasswordBroker;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->latest()
            ->paginate(15);

        return view('admin.users.index', [
            'users' => $users,
            'roleBadges' => $this->roleBadges(),
            'roleLabels' => $this->roleOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => $this->roleOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'kelas' => ['nullable', 'string', 'max:100', 'required_if:role,user'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'kelas' => $validated['kelas'] ?? null,
            'password' => Str::random(64),
            'is_active' => false,
        ]);

        $this->sendActivationInvitation($user);

        $user->syncRoles([$validated['role']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('status', 'User baru berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'kelas' => ['nullable', 'string', 'max:100', 'required_if:role,user'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'kelas' => $validated['kelas'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('status', 'Data user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return redirect()->route('admin.users.index')
                ->with('status', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $user->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('status', 'User berhasil dihapus.');
    }

    public function resendActivation(User $user): RedirectResponse
    {
        if ($user->is_active) {
            return redirect()->route('admin.users.index')
                ->with('status', 'User sudah aktif.');
        }

        $this->sendActivationInvitation($user);

        return redirect()->route('admin.users.index')
            ->with('status', 'Email aktivasi berhasil dikirim ulang.');
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new UsersExport(), 'users-export-' . now()->format('Ymd_His') . '.xlsx');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new TemplateExport(
                ['name', 'email', 'kelas', 'role'],
                ['Rafli Maulana', 'rafli@example.com', 'XII RPL 1', 'user'],
            ),
            'users-template.xlsx',
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('file'));
            
            $errors = $import->getErrors();
            if (!empty($errors)) {
                return redirect()->route('admin.users.index')
                    ->with('status', 'Import user selesai dengan ' . count($errors) . ' baris gagal.')
                    ->with('import_errors', $errors);
            }
        } catch (\Throwable) {
            return redirect()->route('admin.users.index')
                ->with('status', 'Import user gagal. Pastikan file sesuai template.');
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'Import user berhasil diproses.');
    }

    private function roleOptions(): array
    {
        return [
            'admin' => 'Admin',
            'worker' => 'Petugas',
            'user' => 'User',
            'technician' => 'Teknisi',
        ];
    }

    private function roleBadges(): array
    {
        return [
            'admin' => 'bg-emerald-600 text-white ring-emerald-600',
            'worker' => 'bg-rose-600 text-white ring-rose-600',
            'user' => 'bg-sky-600 text-white ring-sky-600',
            'technician' => 'bg-amber-500 text-slate-900 ring-amber-500',
        ];
    }

    private function sendActivationInvitation(User $user): void
    {
        /** @var PasswordBroker $broker */
        $broker = Password::broker();
        $token = $broker->createToken($user);
        $resetUrl = route('activate-account', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new UserActivationMail($user, $resetUrl));
    }
}
