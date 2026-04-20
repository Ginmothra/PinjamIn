<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UsersImport implements ToCollection, WithHeadingRow
{
    private array $errors = [];

    public function collection(Collection $rows): void
    {
        $rowNumber = 2;
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));

            if ($name === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($name === '') {
                    $this->errors[] = ['row' => $rowNumber, 'field' => 'name', 'message' => 'Nama user wajib diisi.'];
                }
                if ($email === '') {
                    $this->errors[] = ['row' => $rowNumber, 'field' => 'email', 'message' => 'Email wajib diisi.'];
                } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = ['row' => $rowNumber, 'field' => 'email', 'message' => 'Format email tidak valid.'];
                }
                $rowNumber++;
                continue;
            }

            $role = $this->normalizeRole($row['role'] ?? null);
            Role::findOrCreate($role, 'web');

            $user = User::query()->firstOrNew(['email' => $email]);
            $user->name = $name;
            $user->kelas = $this->nullableText($row['kelas'] ?? null);
            $user->is_active = $this->normalizeIsActive($row['is_active'] ?? null);

            $password = trim((string) ($row['password'] ?? ''));
            if (! $user->exists) {
                $user->password = Hash::make($password !== '' ? $password : 'password123');
                $user->email_verified_at = now();
            } elseif ($password !== '') {
                $user->password = Hash::make($password);
            }

            $user->save();
            $user->syncRoles([$role]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function normalizeRole(mixed $value): string
    {
        $role = strtolower(trim((string) $value));
        $allowed = ['admin', 'worker', 'user', 'technician'];

        return in_array($role, $allowed, true) ? $role : 'user';
    }

    private function normalizeIsActive(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        $truthy = ['1', 'true', 'yes', 'y', 'aktif', 'active'];
        $falsy = ['0', 'false', 'no', 'n', 'nonaktif', 'inactive'];

        if (in_array($normalized, $truthy, true)) {
            return true;
        }

        if (in_array($normalized, $falsy, true)) {
            return false;
        }

        return true;
    }
}
