<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Reference data a fresh database needs. Safe to run on every deploy
     * (docker/entrypoint.sh): it never creates user accounts and never
     * overwrites data people have edited in the app.
     */
    public function run(): void
    {
        // Executing companies
        Company::firstOrCreate(['name' => 'Precision IT Systems'], ['code' => 'PRECISION', 'is_active' => true]);
        Company::firstOrCreate(['name' => 'NP Solutions'], ['code' => 'NPSOL', 'is_active' => true]);
        Company::firstOrCreate(['name' => 'Linepix'], ['code' => 'LINEPIX', 'is_active' => true]);

        // Starter product catalogue only for an empty catalogue: ProductSeeder resets
        // prices and stock of existing SKUs, so it must not run over live data
        if (Product::query()->doesntExist()) {
            $this->call(ProductSeeder::class);
        }
    }
}
