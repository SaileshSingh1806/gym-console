<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'singhshailesh79@gmail.com'],
            [
                'name' => 'Shailesh Singh',
                'role' => 'super_admin',
                'status' => 'ACTIVE',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@gymconsole.com'],
            [
                'name' => 'SaaS Super Admin',
                'role' => 'super_admin',
                'status' => 'ACTIVE',
                'password' => Hash::make('password'),
            ]
        );
    }
}
