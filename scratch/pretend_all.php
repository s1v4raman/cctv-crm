<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

config(['database.default' => 'pgsql']);
$pdo = new PDO('sqlite::memory:');
$connection = new PostgresConnection($pdo, 'neondb', '');
$grammar = new PostgresGrammar($connection);
$connection->setSchemaGrammar($grammar);

// Mock DB::connection() and Schema facade
DB::swap($connection);
Schema::swap($connection->getSchemaBuilder());

$files = glob(__DIR__ . '/../database/migrations/*.php');
sort($files);

$allQueries = [];

foreach ($files as $file) {
    $name = basename($file, '.php');
    $migration = require $file;
    try {
        $queries = $connection->pretend(function() use ($migration) {
            $migration->up();
        });
        $allQueries[$name] = array_column($queries, 'query');
        echo "OK: $name (" . count($queries) . " queries)\n";
    } catch (\Throwable $e) {
        echo "FAIL: $name - " . $e->getMessage() . "\n";
    }
}

file_put_contents(__DIR__ . '/postgres_all_queries.json', json_encode($allQueries, JSON_PRETTY_PRINT));
echo "Finished pretend run! Total migrations processed: " . count($allQueries) . "\n";
