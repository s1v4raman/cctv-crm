<?php
$start = microtime(true);
$options = [
    PDO::ATTR_TIMEOUT => 5,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => true,
];
$dsn = "pgsql:host=ep-little-firefly-aolx73by-pooler.c-2.ap-southeast-1.aws.neon.tech;port=5432;dbname=neondb;sslmode=require";
$pdo = new PDO($dsn, "neondb_owner", "npg_r3e8jnXIpKmH", $options);
$connTime = round((microtime(true) - $start) * 1000, 2);

$qStart = microtime(true);
for ($i = 0; $i < 5; $i++) {
    $pdo->query("SELECT 1");
}
$batchTime = round((microtime(true) - $qStart) * 1000, 2);

echo "Connection: {$connTime}ms | 5 Queries: {$batchTime}ms (Avg: " . round($batchTime/5, 2) . "ms/query)\n";
