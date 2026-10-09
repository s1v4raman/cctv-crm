<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "TECHNICIANS:\n";
foreach (App\Models\User::where('role', 'technician')->get() as $u) {
    echo "ID: {$u->id} | {$u->name} | {$u->email}\n";
}

echo "\nEMPLOYEES / STAFF:\n";
foreach (App\Models\User::whereIn('role', ['staff', 'technician'])->get() as $u) {
    echo "ID: {$u->id} | {$u->name} | {$u->role}\n";
}

echo "\nPROJECTS & ASSIGNMENTS:\n";
foreach (App\Models\Project::all() as $p) {
    echo "ID: {$p->id} | Code: {$p->project_code} | Title: {$p->title}\n";
    echo "  Status: {$p->status} | Type: {$p->project_type}\n";
    echo "  Site ID: " . ($p->site_id ?? 'null') . "\n";
    echo "  Lead Tech ID: " . ($p->lead_technician_id ?? 'null') . "\n";
    echo "  Assigned To: " . ($p->assigned_to ?? 'null') . "\n";
}
