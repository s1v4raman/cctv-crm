<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Quotation;

echo "--- Testing Dynamic Financial Year Reference Generator ---\n";

// Test Model Instance
$testQuot = new Quotation();
$testQuot->id = 23;
$testQuot->quotation_no = 'QUOT-20261119-0023';

// 1. Date in FY 2026-27 (e.g. Nov 2026)
$testQuot->quotation_date = \Carbon\Carbon::parse('2026-11-19');
echo "FY 2026-27 (Date: 2026-11-19):\n";
echo "  Format 1: " . $testQuot->getFormattedReferenceNo('1') . " (Expected: PITS/QUOT/26-27/23)\n";
echo "  Format 2: " . $testQuot->getFormattedReferenceNo('2') . " (Expected: NP/26-27/023)\n";
echo "  Format 3: " . $testQuot->getFormattedReferenceNo('3') . " (Expected: LP/26-27/23)\n";

// 2. Date in FY 2027-28 (e.g. May 2027)
$testQuot->quotation_date = \Carbon\Carbon::parse('2027-05-10');
echo "\nFY 2027-28 (Date: 2027-05-10):\n";
echo "  Format 1: " . $testQuot->getFormattedReferenceNo('1') . " (Expected: PITS/QUOT/27-28/23)\n";
echo "  Format 2: " . $testQuot->getFormattedReferenceNo('2') . " (Expected: NP/27-28/023)\n";
echo "  Format 3: " . $testQuot->getFormattedReferenceNo('3') . " (Expected: LP/27-28/23)\n";

// 3. Date in FY 2026-27 (Jan 2027 - Before April)
$testQuot->quotation_date = \Carbon\Carbon::parse('2027-01-15');
echo "\nFY 2026-27 (Date: 2027-01-15):\n";
echo "  Format 1: " . $testQuot->getFormattedReferenceNo('1') . " (Expected: PITS/QUOT/26-27/23)\n";
echo "  Format 2: " . $testQuot->getFormattedReferenceNo('2') . " (Expected: NP/26-27/023)\n";
echo "  Format 3: " . $testQuot->getFormattedReferenceNo('3') . " (Expected: LP/26-27/23)\n";

echo "\n--- Generating PDF Previews for Formats 1, 2, and 3 ---\n";

$artifactDir = 'C:/Users/ADMIN/.gemini/antigravity/brain/fab41074-68c4-45bb-bc7c-3c7b98b164a7';
@mkdir('public/demo', 0777, true);

// ==========================================
// FORMAT 1 (Precision IT Systems)
// ==========================================
// Sample data
$f1Quot = new Quotation();
$f1Quot->id = 58;
$f1Quot->quotation_no = 'PITS/QUOT/26-27/58';
$f1Quot->quotation_date = \Carbon\Carbon::parse('2026-08-06');
$f1Quot->pricing_mode = 'itemized';
$f1Quot->subtotal = 28350.00;
$f1Quot->tax_percent = 18;
$f1Quot->tax_amount = 5103.00;
$f1Quot->total = 33453.00;
$f1Quot->setRelation('lead', (object)[
    'customer_name' => 'TRUE WAVES ASSOCIATES',
    'company_name' => 'TRUE WAVES ASSOCIATES',
    'site_address' => "27/5, Easwaran Koil Street, Vellai Thottam, West\nMambalam, Chennai, Greater Chennai, Tamil Nadu\n600033"
]);

$items1 = [
    ['item_name' => '8 Port Network Switch', 'description' => null, 'quantity' => 1, 'unit_price' => 1300.00, 'discount_rate' => 1300.00, 'total' => 1300.00],
    ['item_name' => 'IO SET', 'description' => null, 'quantity' => 4, 'unit_price' => 300.00, 'discount_rate' => 300.00, 'total' => 1200.00],
    ['item_name' => 'CAT6 Cable in Metrs', 'description' => null, 'quantity' => 90, 'unit_price' => 35.00, 'discount_rate' => 35.00, 'total' => 3150.00],
    ['item_name' => '2M Patch Cable', 'description' => null, 'quantity' => 4, 'unit_price' => 150.00, 'discount_rate' => 150.00, 'total' => 600.00],
    ['item_name' => 'Tplink CPE 510 Wire Less Bridge', 'description' => null, 'quantity' => 2, 'unit_price' => 3400.00, 'discount_rate' => 3250.00, 'total' => 6500.00],
    ['item_name' => 'PVC Pipe and Accessories LS', 'description' => null, 'quantity' => 1, 'unit_price' => 1000.00, 'discount_rate' => 1000.00, 'total' => 1000.00],
    ['item_name' => 'Pole Extension for the Bridge', 'description' => null, 'quantity' => 2, 'unit_price' => 750.00, 'discount_rate' => 750.00, 'total' => 1500.00],
    ['item_name' => 'Hikvision Access Control Biometric Device', 'description' => 'Model No: DS-K1T343MFWX', 'quantity' => 1, 'unit_price' => 8700.00, 'discount_rate' => 8100.00, 'total' => 8100.00],
    ['item_name' => 'Cable Laying & Camera checking and Fiber', 'description' => "Termination and Bridge Instllation &\nBiometric Instllation", 'quantity' => 1, 'unit_price' => 6000.00, 'discount_rate' => 5000.00, 'total' => 5000.00]
];
$f1Quot->setRelation('items', collect(array_map(fn($it) => (object)$it, $items1)));

$pdf1Sample = Pdf::loadView('quotations.pdf.format1', ['quotation' => $f1Quot])->output();
file_put_contents('public/demo/preview_format1_sample.pdf', $pdf1Sample);
file_put_contents($artifactDir . '/preview_format1_sample.pdf', $pdf1Sample);

// Format 1 CRM dynamic version with fractional paise (e.g. 23566.50 -> 23567.00)
$f1Crm = clone $f1Quot;
$f1Crm->quotation_no = 'QUOT-20261119-0023';
$f1Crm->quotation_date = \Carbon\Carbon::parse('2026-11-19');
$f1Crm->subtotal = 19971.61;
$f1Crm->tax_percent = 18;
$f1Crm->tax_amount = 3594.89;
$f1Crm->total = 23566.50; // Total 23,566.50 rounds to 23,567.00 with Round off: + ₹ 0.50
$pdf1Crm = Pdf::loadView('quotations.pdf.format1', ['quotation' => $f1Crm])->output();
file_put_contents('public/demo/preview_format1_crm.pdf', $pdf1Crm);
file_put_contents($artifactDir . '/preview_format1_crm.pdf', $pdf1Crm);


// ==========================================
// FORMAT 2 (NP Solutions)
// ==========================================
$f2Quot = new Quotation();
$f2Quot->id = 3;
$f2Quot->quotation_no = 'NP/26-27/003';
$f2Quot->quotation_date = \Carbon\Carbon::parse('2026-07-29');
$f2Quot->subtotal = 150335.00;
$f2Quot->tax_percent = 18;
$f2Quot->tax_amount = 27060.30;
$f2Quot->total = 177395.30;
$f2Quot->setRelation('lead', (object)[
    'customer_name' => 'ACS MEDICAL COLLEGE & HOSPITAL',
    'company_name' => 'ACS MEDICAL COLLEGE & HOSPITAL',
    'site_address' => "PERIYAR EVR HIGH ROAD, NH 4,\nCHENNAI BANGALORE HIGHWAYS\nVELAPPANCHAVADI, CHENNAI - 600 077"
]);

$items2 = [
    ['item_name' => 'CAT6 UTP CABLE 305M BLUE D-LINK', 'hsn_code' => '8544', 'quantity' => 12, 'unit' => 'BND', 'unit_price' => 7450.00, 'gst_percent' => 18, 'gst_amount' => 16092.00, 'total' => 105492.00],
    ['item_name' => 'UTP PATCH CORD 1MTR D-LINK', 'hsn_code' => '8544', 'quantity' => 10, 'unit' => 'NOS', 'unit_price' => 110.00, 'gst_percent' => 18, 'gst_amount' => 198.00, 'total' => 1298.00],
    ['item_name' => 'UTP PATCH CORD 2MTR D-LINK', 'hsn_code' => '8544', 'quantity' => 10, 'unit' => 'NOS', 'unit_price' => 140.00, 'gst_percent' => 18, 'gst_amount' => 252.00, 'total' => 1652.00],
    ['item_name' => 'UTP KEYSTONE JACK CAT 6 D-LINK', 'hsn_code' => '8536', 'quantity' => 60, 'unit' => 'NOS', 'unit_price' => 145.00, 'gst_percent' => 18, 'gst_amount' => 1566.00, 'total' => 10266.00],
    ['item_name' => 'RJ45 CONNECTOR CAT 6 D-LINK', 'hsn_code' => '8536', 'quantity' => 50, 'unit' => 'NOS', 'unit_price' => 6.50, 'gst_percent' => 18, 'gst_amount' => 58.50, 'total' => 383.50],
    ['item_name' => '24 PORT CAT6 UTP LOADED PATCH PANEL D-LINK', 'hsn_code' => '8536', 'quantity' => 2, 'unit' => 'NOS', 'unit_price' => 3100.00, 'gst_percent' => 18, 'gst_amount' => 1116.00, 'total' => 7316.00],
    ['item_name' => 'PATCH PANEL CABLE MANAGER', 'hsn_code' => '8536', 'quantity' => 2, 'unit' => 'NOS', 'unit_price' => 350.00, 'gst_percent' => 18, 'gst_amount' => 126.00, 'total' => 826.00],
    ['item_name' => '1" PVC PIPE HEAVY DUTY', 'hsn_code' => '3917', 'quantity' => 60, 'unit' => 'NOS', 'unit_price' => 95.00, 'gst_percent' => 18, 'gst_amount' => 1026.00, 'total' => 6726.00],
    ['item_name' => '1" ACCESSORIES LS', 'hsn_code' => '3917', 'quantity' => 1, 'unit' => 'LOT', 'unit_price' => 1500.00, 'gst_percent' => 18, 'gst_amount' => 270.00, 'total' => 1770.00],
    ['item_name' => 'CAT6 UTP CABLE LAYING PER METER CHARGES', 'hsn_code' => '9987', 'quantity' => 2200, 'unit' => 'MTR', 'unit_price' => 12.00, 'gst_percent' => 18, 'gst_amount' => 4752.00, 'total' => 31152.00],
    ['item_name' => '1" PVC CASING & CAPPING INSTALLATION CHARGES', 'hsn_code' => '9987', 'quantity' => 200, 'unit' => 'MTR', 'unit_price' => 25.00, 'gst_percent' => 18, 'gst_amount' => 900.00, 'total' => 5900.00],
    ['item_name' => 'INFORMATION OUTLET TERMINATION CHARGES', 'hsn_code' => '9987', 'quantity' => 24, 'unit' => 'NOS', 'unit_price' => 75.00, 'gst_percent' => 18, 'gst_amount' => 324.00, 'total' => 2124.00],
    ['item_name' => 'PATCH PANEL TERMINATION CHARGES', 'hsn_code' => '9987', 'quantity' => 2, 'unit' => 'NOS', 'unit_price' => 500.00, 'gst_percent' => 18, 'gst_amount' => 180.00, 'total' => 1180.00],
    ['item_name' => 'TESTING AND COMMISSIONING CHARGES', 'hsn_code' => '9987', 'quantity' => 1, 'unit' => 'LOT', 'unit_price' => 1500.00, 'gst_percent' => 18, 'gst_amount' => 270.00, 'total' => 1770.00]
];
$f2Quot->setRelation('items', collect(array_map(fn($it) => (object)$it, $items2)));

$pdf2Sample = Pdf::loadView('quotations.pdf.format2', ['quotation' => $f2Quot])->output();
file_put_contents('public/demo/preview_format2_sample.pdf', $pdf2Sample);
file_put_contents($artifactDir . '/preview_format2_sample.pdf', $pdf2Sample);

// Format 2 CRM dynamic version
$f2Crm = clone $f2Quot;
$f2Crm->quotation_no = 'QUOT-20261119-0023';
$f2Crm->quotation_date = \Carbon\Carbon::parse('2026-11-19');
$pdf2Crm = Pdf::loadView('quotations.pdf.format2', ['quotation' => $f2Crm])->output();
file_put_contents('public/demo/preview_format2_crm.pdf', $pdf2Crm);
file_put_contents($artifactDir . '/preview_format2_crm.pdf', $pdf2Crm);


// ==========================================
// FORMAT 3 (Linepix Tax Invoice)
// ==========================================
$f3Quot = new Quotation();
$f3Quot->id = 2;
$f3Quot->quotation_no = 'LP/26-27/02';
$f3Quot->quotation_date = \Carbon\Carbon::parse('2026-07-29');
$f3Quot->subtotal = 171000.00;
$f3Quot->tax_percent = 18;
$f3Quot->tax_amount = 30780.00;
$f3Quot->total = 201780.00;
$f3Quot->setRelation('lead', (object)[
    'customer_name' => 'ACS MEDICAL COLLEGE AND HOSPITAL',
    'company_name' => 'ACS MEDICAL COLLEGE AND HOSPITAL',
    'site_address' => "Periyar EVR High Road, NH 4, Chennai - Bangalore\nHighway, Velappanchavadi, Chennai - 600 077"
]);

$items3 = [
    [
        'item_name' => 'Pro - Pupil - Student Attendance Management System V2',
        'hsn_sac' => null,
        'quantity' => 1,
        'unit' => 'Nos',
        'unit_price' => 156000.00,
        'gst_percent' => 18,
        'gst_amount' => 28080.00,
        'total' => 184080.00
    ],
    [
        'item_name' => 'Student Data Management and Configuration',
        'hsn_sac' => null,
        'quantity' => 1,
        'unit' => 'Nos',
        'unit_price' => 15000.00,
        'gst_percent' => 18,
        'gst_amount' => 2700.00,
        'total' => 17700.00
    ]
];
$f3Quot->setRelation('items', collect(array_map(fn($it) => (object)$it, $items3)));

$pdf3Sample = Pdf::loadView('quotations.pdf.format3', ['quotation' => $f3Quot])->output();
file_put_contents('public/demo/preview_format3_sample.pdf', $pdf3Sample);
file_put_contents($artifactDir . '/preview_format3_sample.pdf', $pdf3Sample);

// Format 3 CRM dynamic version with fractional paise (e.g. 48645.50 -> 48646.00)
$f3Crm = clone $f3Quot;
$f3Crm->quotation_no = 'QUOT-20261119-0023';
$f3Crm->quotation_date = \Carbon\Carbon::parse('2026-11-19');
$f3Crm->subtotal = 41225.00;
$f3Crm->tax_percent = 18;
$f3Crm->tax_amount = 7420.50;
$f3Crm->total = 48645.50; // Total 48,645.50 rounds to 48,646.00 with Round off: + ₹ 0.50
$pdf3Crm = Pdf::loadView('quotations.pdf.format3', ['quotation' => $f3Crm])->output();
file_put_contents('public/demo/preview_format3_crm.pdf', $pdf3Crm);
file_put_contents($artifactDir . '/preview_format3_crm.pdf', $pdf3Crm);

echo "All PDF previews generated successfully!\n";

foreach (glob('public/demo/preview_format*.pdf') as $f) {
    $count = preg_match_all('/\/Type\s*\/Page\b/', file_get_contents($f));
    echo basename($f) . ": " . $count . " page(s)\n";
}
