<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('docs.data_flow_pdf');
$pdf->setPaper('a4', 'portrait');

$dir = public_path('docs');
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$outputPath = public_path('docs/CCTV_CRM_Module_Data_Flow_Diagram.pdf');
$pdf->save($outputPath);

// Also copy to storage/app/public
$storageDir = storage_path('app/public/docs');
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}
copy($outputPath, storage_path('app/public/docs/CCTV_CRM_Module_Data_Flow_Diagram.pdf'));

echo "SUCCESS: PDF generated at: " . $outputPath . PHP_EOL;
echo "File size: " . filesize($outputPath) . " bytes" . PHP_EOL;
