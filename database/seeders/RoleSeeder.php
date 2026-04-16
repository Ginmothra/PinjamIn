<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'petugas' => 'worker',
            'teknisi' => 'technician',
        ] as $oldRoleName => $newRoleName) {
            $oldRole = Role::query()->where('name', $oldRoleName)->where('guard_name', 'web')->first();

            if ($oldRole && ! Role::query()->where('name', $newRoleName)->where('guard_name', 'web')->exists()) {
                $oldRole->update(['name' => $newRoleName]);
            }
        }

        foreach (['admin', 'worker', 'user', 'technician'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}
