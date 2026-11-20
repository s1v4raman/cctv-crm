<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

// 1. Build Mock Sample Object matching exact reference
$sampleQuotation = new \stdClass();
$sampleQuotation->quotation_no = 'PITS/QUOT/26-21/58';
$sampleQuotation->quotation_date = '2026-08-06';
$sampleQuotation->pricing_mode = 'itemized';
$sampleQuotation->subtotal = 28350.00;
$sampleQuotation->tax_percent = 18;
$sampleQuotation->tax_amount = 5103.00;
$sampleQuotation->total = 33453.00;
$sampleQuotation->lead = (object)[
    'customer_name' => 'TRUE WAVES ASSOCIATES',
    'company_name' => 'TRUE WAVES ASSOCIATES',
    'site_address' => "27/5, Easwaran Koil Street, Vellai Thottam, West\nMambalam, Chennai, Greater Chennai, Tamil Nadu\n600033"
];

$itemsData = [
    [
        'item_name' => '8 Port Network Switch',
        'description' => null,
        'quantity' => 1,
        'unit_price' => 1300.00,
        'discount_rate' => 1300.00,
        'total' => 1300.00
    ],
    [
        'item_name' => 'IO SET',
        'description' => null,
        'quantity' => 4,
        'unit_price' => 300.00,
        'discount_rate' => 300.00,
        'total' => 1200.00
    ],
    [
        'item_name' => 'CAT6 Cable in Metrs',
        'description' => null,
        'quantity' => 90,
        'unit_price' => 35.00,
        'discount_rate' => 35.00,
        'total' => 3150.00
    ],
    [
        'item_name' => '2M Patch Cable',
        'description' => null,
        'quantity' => 4,
        'unit_price' => 150.00,
        'discount_rate' => 150.00,
        'total' => 600.00
    ],
    [
        'item_name' => 'Tplink CPE 510 Wire Less Bridge',
        'description' => null,
        'quantity' => 2,
        'unit_price' => 3400.00,
        'discount_rate' => 3250.00,
        'total' => 6500.00
    ],
    [
        'item_name' => 'PVC Pipe and Accessories LS',
        'description' => null,
        'quantity' => 1,
        'unit_price' => 1000.00,
        'discount_rate' => 1000.00,
        'total' => 1000.00
    ],
    [
        'item_name' => 'Pole Extension for the Bridge',
        'description' => null,
        'quantity' => 2,
        'unit_price' => 750.00,
        'discount_rate' => 750.00,
        'total' => 1500.00
    ],
    [
        'item_name' => 'Hikvision Access Control Biometric Device',
        'description' => 'Model No: DS-K1T343MFWX',
        'quantity' => 1,
        'unit_price' => 8700.00,
        'discount_rate' => 8100.00,
        'total' => 8100.00
    ],
    [
        'item_name' => 'Cable Laying & Camera checking and Fiber',
        'description' => "Termination and Bridge Instllation &\nBiometric Instllation",
        'quantity' => 1,
        'unit_price' => 6000.00,
        'discount_rate' => 5000.00,
        'total' => 5000.00
    ]
];

$sampleQuotation->items = collect(array_map(fn($it) => (object)$it, $itemsData));

// Generate Sample 1
$pdf1 = Pdf::loadView('quotations.pdf.format1', ['quotation' => $sampleQuotation]);
$pdf1Content = $pdf1->output();

// 2. Build CRM Version (QUOT-20261119-0023 and Date 19-Nov-26)
$crmQuotation = clone $sampleQuotation;
$crmQuotation->quotation_no = 'QUOT-20261119-0023';
$crmQuotation->quotation_date = '2026-11-19';

$pdf2 = Pdf::loadView('quotations.pdf.format1', ['quotation' => $crmQuotation]);
$pdf2Content = $pdf2->output();

// Ensure output dirs exist
@mkdir('public/demo', 0777, true);
$artifactDir = 'C:/Users/ADMIN/.gemini/antigravity/brain/fab41074-68c4-45bb-bc7c-3c7b98b164a7';

// Save files
file_put_contents('public/demo/preview_format1_sample.pdf', $pdf1Content);
file_put_contents('public/demo/preview_format1_crm.pdf', $pdf2Content);

file_put_contents($artifactDir . '/preview_format1_sample.pdf', $pdf1Content);
file_put_contents($artifactDir . '/preview_format1_crm.pdf', $pdf2Content);

echo "Successfully generated:\n";
echo "1. " . $artifactDir . "/preview_format1_sample.pdf (" . strlen($pdf1Content) . " bytes)\n";
echo "2. " . $artifactDir . "/preview_format1_crm.pdf (" . strlen($pdf2Content) . " bytes)\n";
echo "3. public/demo/preview_format1_sample.pdf\n";
echo "4. public/demo/preview_format1_crm.pdf\n";
