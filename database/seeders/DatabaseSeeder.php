<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ensure product catalog is populated (non-destructive updateOrCreate)
        $this->call(ProductSeeder::class);

        // 2. Ensure the 3 standard system users exist with active credentials
        \App\Models\User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'alex@example.com'],
            [
                'name' => 'Alex Rivera',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Miller',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'technician',
                'email_verified_at' => now(),
            ]
        );

        // 3. Ensure master executing companies exist
        \App\Models\Company::firstOrCreate(['name' => 'Precision IT Systems'], ['code' => 'PRECISION', 'is_active' => true]);
        \App\Models\Company::firstOrCreate(['name' => 'NP Solutions'], ['code' => 'NPSOL', 'is_active' => true]);
        \App\Models\Company::firstOrCreate(['name' => 'Linepix'], ['code' => 'LINEPIX', 'is_active' => true]);
    }
}
