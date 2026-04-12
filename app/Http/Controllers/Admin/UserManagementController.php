<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

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
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'approved',
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

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
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
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

    private function roleOptions(): array
    {
        return [
            'admin' => 'Admin',
            'petugas' => 'Petugas',
            'user' => 'User',
            'teknisi' => 'Teknisi',
        ];
    }

    private function roleBadges(): array
    {
        return [
            'admin' => 'bg-emerald-600 text-white ring-emerald-600',
            'petugas' => 'bg-rose-600 text-white ring-rose-600',
            'user' => 'bg-sky-600 text-white ring-sky-600',
            'teknisi' => 'bg-amber-400 text-slate-900 ring-amber-400',
        ];
    }
}
