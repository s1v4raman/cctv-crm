<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$pdo = new PDO('sqlite::memory:');
$conn = new PostgresConnection($pdo, 'neondb', '');
$conn->setSchemaGrammar(new PostgresGrammar($conn));
DB::swap($conn);
Schema::swap($conn->getSchemaBuilder());

echo 'Driver name: ' . Schema::getConnection()->getDriverName() . PHP_EOL;

$migration = require __DIR__ . '/../database/migrations/2026_08_23_160903_add_expired_status_to_quotations_table.php';
$queries = $conn->pretend(function() use ($migration) {
    $migration->up();
});
print_r($queries);
