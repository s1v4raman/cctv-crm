<?php
$start = microtime(true);
$options = [
    PDO::ATTR_TIMEOUT => 5,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => true,
];
// Connection details come from the environment, never from this file
$dsn = getenv('DB_DSN') ?: 'pgsql:host=' . getenv('DB_HOST') . ';port=5432;dbname=' . (getenv('DB_DATABASE') ?: 'neondb') . ';sslmode=require';
$pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), $options);
$connTime = round((microtime(true) - $start) * 1000, 2);

$qStart = microtime(true);
for ($i = 0; $i < 5; $i++) {
    $pdo->query("SELECT 1");
}
$batchTime = round((microtime(true) - $qStart) * 1000, 2);

echo "Connection: {$connTime}ms | 5 Queries: {$batchTime}ms (Avg: " . round($batchTime/5, 2) . "ms/query)\n";
