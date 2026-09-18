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
                'name' => 'GymConsole',
                'role' => 'super_admin',
                'status' => 'ACTIVE',
                'password' => Hash::make('Admin@123'),
            ]
        );
    }
}
