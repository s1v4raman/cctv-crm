<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('docs.admin_technician_workflow_pdf');
$pdf->setPaper('a4', 'portrait');

$dir = public_path('docs');
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$outputPath = public_path('docs/Admin_to_Technician_Workflow_Guide.pdf');
$pdf->save($outputPath);

// Also copy to storage/app/public/docs
$storageDir = storage_path('app/public/docs');
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}
copy($outputPath, storage_path('app/public/docs/Admin_to_Technician_Workflow_Guide.pdf'));

echo "SUCCESS: PDF generated at: " . $outputPath . PHP_EOL;
echo "File size: " . filesize($outputPath) . " bytes" . PHP_EOL;
