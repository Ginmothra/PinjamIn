<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $admin->assignRole('admin');

        $technician = User::factory()->create([
            'name' => 'technician',
            'email' => 'tech@test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $technician->assignRole('technician');

        $worker = User::factory()->create([
            'name' => 'worker',
            'email' => 'worker@test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $worker->assignRole('worker');
    }
}
