<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'trainer@example.com'],
            [
                'name' => 'System Trainer',
                'password' => Hash::make('password'),
                'role' => 'trainer',
            ]
        );

        User::updateOrCreate(
            ['email' => 'trainee@example.com'],
            [
                'name' => 'System Trainee',
                'password' => Hash::make('password'),
                'role' => 'trainee',
            ]
        );
    }
}
