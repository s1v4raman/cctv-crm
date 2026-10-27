<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$files = glob(__DIR__ . '/../database/migrations/*.php');
sort($files);

echo "Found " . count($files) . " migration files.\n";

$connection = DB::connection('sqlite');
$grammar = new \Illuminate\Database\Schema\Grammars\PostgresGrammar($connection);

foreach ($files as $file) {
    $migration = require $file;
    echo basename($file) . ": " . get_class($migration) . "\n";
}
