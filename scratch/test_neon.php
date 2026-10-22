<?php
try {
    $pdo = new PDO('pgsql:host=ep-odd-sky-b55wmpnc-pooler.c-7.us-east-2.aws.neon.tech;port=5432;dbname=neondb;sslmode=require', 'neondb_owner', 'npg_hb9lqafutc2E');
    echo "Connected successfully to Neon!\n";
    $stmt = $pdo->query("SELECT count(*) FROM products");
    echo "Product count: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
