<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

config(['database.default' => 'sqlite']);
config(['database.connections.sqlite' => [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]]);
\Illuminate\Support\Facades\DB::purge();
\Illuminate\Support\Facades\Artisan::call('migrate');

$p1 = \App\Models\Project::create([
    'id' => 1,
    'project_code' => 'PRJ-001',
    'title' => 'A.C.S. Medical College',
    'company_name' => 'A.C.S. Medical College',
    'site_address' => 'Chennai',
    'status' => 'active',
]);
$p2 = \App\Models\Project::create([
    'id' => 2,
    'project_code' => 'PRJ-002',
    'title' => 'A.C.S. Medical College',
    'company_name' => 'A.C.S. Medical College',
    'site_address' => 'Chennai',
    'status' => 'active',
]);
$p3 = \App\Models\Project::create([
    'id' => 3,
    'project_code' => 'PRJ-003',
    'title' => 'Dr.M.G.R University',
    'company_name' => 'Prabhu',
    'site_address' => 'Chennai',
    'status' => 'active',
]);

$admin = \App\Models\User::factory()->create(['id' => 1, 'role' => 'admin']);
$worker = \App\Models\User::factory()->create(['id' => 6, 'role' => 'worker']);
$technician = \App\Models\User::factory()->create(['id' => 7, 'role' => 'technician']);
$staff = \App\Models\User::factory()->create(['id' => 8, 'role' => 'staff']);

\App\Models\ProjectTask::create([
    'id' => 1,
    'project_id' => 2,
    'phase_name' => 'Phase 2',
    'title' => 'cabling',
    'assigned_to' => 6,
    'priority' => 'medium',
    'status' => 'in_progress',
    'due_date' => null,
    'start_date' => '2026-10-17',
    'shift' => 'Day Shift',
    'order_column' => 1,
]);

\App\Models\ProjectTask::create([
    'id' => 14,
    'project_id' => 3,
    'phase_name' => 'Phase 1',
    'title' => 'Cable Tray',
    'assigned_to' => null,
    'priority' => 'medium',
    'status' => 'completed',
    'due_date' => null,
    'start_date' => '2026-10-26',
    'shift' => 'Day Shift',
    'order_column' => 1,
]);

$users = ['admin' => $admin, 'worker' => $worker, 'technician' => $technician, 'staff' => $staff];

foreach ($users as $roleName => $u) {
    $req = \Illuminate\Http\Request::create('/calendar', 'GET');
    $app->instance('request', $req);
    \Illuminate\Support\Facades\Auth::login($u);
    $res = $kernel->handle($req);
    echo "User role [{$roleName}]: status " . $res->getStatusCode() . "\n";
    if ($res->getStatusCode() !== 200) {
        echo substr($res->getContent(), 0, 500) . "\n";
    }
}
