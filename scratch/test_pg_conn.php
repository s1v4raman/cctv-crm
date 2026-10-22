<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;

$pdo = new PDO('sqlite::memory:');
$connection = new PostgresConnection($pdo, 'neondb', 'public');
$grammar = new PostgresGrammar($connection);
$connection->setSchemaGrammar($grammar);

echo "PostgresConnection created successfully!\n";
