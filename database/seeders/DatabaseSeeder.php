<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Tidak digunakan di sini, tapi bisa berguna nanti

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );

        $akuntingRole = Role::firstOrCreate(
            ['name' => 'akunting'],
            ['guard_name' => 'web']
        );

        $csRole = Role::firstOrCreate(
            ['name' => 'cs'],
            ['guard_name' => 'web']
        );

        $adminUser = User::create([
            'name' => 'Admin Super',
            'email' => 'admin@bankdptaspen.co.id',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $adminUser->assignRole($adminRole);

        $akuntingUser = User::create([
            'name' => 'Muhammad Yassin',
            'email' => 'm.yassin@bankdptaspen.co.id',
            'password' => Hash::make('12345678'),
        ]);

        $akuntingUser->assignRole($akuntingRole);

        $csUser = User::create([
            'name' => 'Customer Service',
            'email' => 'cs@bankdptaspen.co.id',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        $csUser->assignRole($csRole);

    }
}
