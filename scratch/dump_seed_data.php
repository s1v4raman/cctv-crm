<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\User;
use App\Models\Company;

$data = [
    'products' => Product::all()->toArray(),
    'users' => User::all()->makeVisible(['password', 'remember_token'])->toArray(),
    'companies' => Company::all()->toArray(),
];

file_put_contents(__DIR__ . '/seed_data.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Dumped " . count($data['products']) . " products, " . count($data['users']) . " users, " . count($data['companies']) . " companies.\n";
