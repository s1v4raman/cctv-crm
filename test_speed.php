<?php
$start = microtime(true);
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$bootTime = round(microtime(true) - $start, 3);

$t2 = microtime(true);
\Illuminate\Support\Facades\DB::table('users')->count();
$dbTime = round(microtime(true) - $t2, 3);

echo "Bootstrap: {$bootTime}s | DB: {$dbTime}s | Total: " . round(microtime(true) - $start, 3) . "s\n";
