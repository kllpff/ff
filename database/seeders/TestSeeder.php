<?php

namespace Database\Seeders;

use App\Models\User;
use FF\Database\Seeder;
use FF\Security\Hash;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => Hash::make('secret123'),
        ]);
    }
}