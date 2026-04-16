<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return User::query()
            ->with('roles')
            ->orderBy('name')
            ->get()
            ->map(static fn (User $user): array => [
                'name' => $user->name,
                'email' => $user->email,
                'kelas' => $user->kelas,
                'role' => $user->roles->pluck('name')->first(),
                'status' => $user->status,
            ]);
    }

    public function headings(): array
    {
        return ['name', 'email', 'kelas', 'role', 'status'];
    }
}
