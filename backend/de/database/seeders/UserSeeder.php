<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@company.de',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'company_name' => 'Recruitment GmbH',
            'country' => 'Germany',
            'is_active' => true,
            'preferred_language' => 'en',
        ]);

        User::create([
            'name' => 'Hans Müller',
            'email' => 'client1@mueller.de',
            'password' => Hash::make('client123'),
            'role' => 'client',
            'company_name' => 'Müller GmbH',
            'country' => 'Germany',
            'is_active' => true,
            'preferred_language' => 'de',
        ]);

        User::create([
            'name' => 'Anna Schmidt',
            'email' => 'client2@schmidt.de',
            'password' => Hash::make('client123'),
            'role' => 'client',
            'company_name' => 'Schmidt AG',
            'country' => 'Germany',
            'is_active' => true,
            'preferred_language' => 'de',
        ]);

        User::create([
            'name' => 'Jan de Vries',
            'email' => 'client3@hospital.nl',
            'password' => Hash::make('client123'),
            'role' => 'client',
            'company_name' => 'Amsterdam Hospital',
            'country' => 'Netherlands',
            'is_active' => true,
            'preferred_language' => 'en',
        ]);
    }
}
