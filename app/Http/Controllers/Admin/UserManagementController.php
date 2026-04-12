<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
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
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
            'rejection_reason' => $validated['status'] === 'rejected' ? ($validated['rejection_reason'] ?? null) : null,
        ]);

        if ($validated['status'] === 'approved') {
            $user->approved_by = $request->user()->id;
            $user->approved_at = now();
        }

        if ($validated['status'] !== 'approved') {
            $user->approved_by = null;
            $user->approved_at = null;
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.users.index')
            ->with('status', 'Data user berhasil diperbarui.');
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $user->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'User berhasil di-approve.');
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $user->update([
            'status' => 'rejected',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'User berhasil di-reject.');
    }
}
