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
        // Ensure Products and 1-Month Full Demo Analytics Data are seeded
        $this->call([
            ProductSeeder::class,
            DemoAnalyticsSeeder::class,
        ]);
    }
}
