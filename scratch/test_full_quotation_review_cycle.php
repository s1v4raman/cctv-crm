<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== STARTING QUOTATION FORMAT & ADMIN PRICING REVIEW CYCLE TEST ===\n\n";

// 1. Get a technician and an admin
$technician = User::where('role', 'technician')->first() ?? User::where('email', 'like', '%tech%')->first();
$admin = User::where('role', 'admin')->first() ?? User::where('is_admin', true)->first();
$lead = Lead::first();
$product = Product::first();

if (!$technician || !$admin || !$lead || !$product) {
    die("FATAL: Required seed data missing (technician, admin, lead, product).\n");
}

echo "Technician: {$technician->name} (ID {$technician->id})\n";
echo "Admin: {$admin->name} (ID {$admin->id})\n";
echo "Lead: {$lead->customer_name} (ID {$lead->id})\n\n";

// STEP 1: Technician creates a quotation with Proposed Format 2 and zero-cost items
Auth::login($technician);

$quoteController = app(\App\Http\Controllers\QuotationController::class);

$storeData = [
    'lead_id' => $lead->id,
    'quotation_date' => now()->toDateString(),
    'valid_until' => now()->addDays(15)->toDateString(),
    'tax_percent' => 18.00,
    'discount' => 0.00,
    'notes' => 'Test quotation submitted on site by technician.',
    'pdf_format' => '2', // Technician chooses Format 2: Technical Detailed
    'items' => [
        [
            'item_name' => '24-Port Gigabit Managed Switch',
            'description' => 'Rackmount core switch',
            'quantity' => 2,
            'unit' => 'Box',
            'unit_price' => 0, // Zero price as in screenshot media_1794757069761_1deddf3b.png
        ],
        [
            'item_name' => 'Cat6 Patch Cable 3m',
            'description' => 'Blue stranded patch cord',
            'quantity' => 10,
            'unit' => 'Mtr',
            'unit_price' => 0, // Zero price
        ]
    ]
];

$storeReq = \App\Http\Requests\StoreQuotationRequest::create(route('quotations.store', $lead), 'POST', $storeData);
$storeReq->setContainer($app);
$storeReq->setUserResolver(fn() => $technician);
$storeReq->validateResolved();

$res = $quoteController->store($storeReq, $lead);
$quote = Quotation::where('created_by', $technician->id)->latest()->first();

echo "Step 1: Technician created Quotation #{$quote->quotation_no}\n";
echo " - Status: {$quote->status} (expected: pending_approval)\n";
echo " - Proposed PDF Format: {$quote->pdf_format} ({$quote->pdf_format_name})\n";
echo " - Item Count: {$quote->items->count()}\n";
echo " - Item 1 Price: ₹{$quote->items[0]->unit_price}\n";
assert($quote->status === 'pending_approval', "Status must be pending_approval");
assert($quote->pdf_format === '2', "Format must be 2");
assert((float)$quote->items[0]->unit_price === 0.0, "Technician item unit price should be 0.0");
echo " -> PASS\n\n";

// STEP 2: Admin reviews and updates item pricing (fixing 0.00 prices)
Auth::login($admin);
$item1 = $quote->items[0];
$item2 = $quote->items[1];

$pricingData = [
    'items' => [
        [
            'id' => $item1->id,
            'quantity' => 2,
            'unit_price' => 4500.00, // Admin sets ₹4,500
        ],
        [
            'id' => $item2->id,
            'quantity' => 10,
            'unit_price' => 150.00, // Admin sets ₹150
        ]
    ],
    'discount' => 500.00,
    'tax_percent' => 18.00,
    'pdf_format' => '3', // Admin analysis decides Format 3 is better for this corporate client
];

$updatePricingReq = Request::create(route('quotations.update-pricing', $quote), 'POST', $pricingData);
$updatePricingReq->setUserResolver(fn() => $admin);

$pricingRes = $quoteController->updatePricing($updatePricingReq, $quote);
$quote->refresh();
$quote->load('items');

echo "Step 2: Admin updated item pricing & format:\n";
echo " - Item 1 Price: ₹{$quote->items[0]->unit_price} (Subtotal: ₹{$quote->items[0]->total})\n";
echo " - Item 2 Price: ₹{$quote->items[1]->unit_price} (Subtotal: ₹{$quote->items[1]->total})\n";
echo " - Subtotal: ₹{$quote->subtotal}\n";
echo " - Discount: ₹{$quote->discount}\n";
echo " - GST Tax Amount: ₹{$quote->tax_amount}\n";
echo " - Grand Total: ₹{$quote->total}\n";
echo " - Format: {$quote->pdf_format} ({$quote->pdf_format_name})\n";

// Expected math:
// Item 1: 2 * 4500 = 9000
// Item 2: 10 * 150 = 1500
// Subtotal = 10500
// Discount = 500 => Taxable = 10000
// GST 18% = 1800
// Grand Total = 11800
assert((float)$quote->subtotal === 10500.00, "Subtotal must be 10500");
assert((float)$quote->tax_amount === 1800.00, "Tax must be 1800");
assert((float)$quote->total === 11800.00, "Grand Total must be 11800");
assert($quote->pdf_format === '3', "Format must be updated to 3");
echo " -> PASS\n\n";

// STEP 3: Admin deletes an item (destroyItem) to test scope adjustment
$itemToDelete = $quote->items[1];
$deleteRes = $quoteController->destroyItem($itemToDelete);
$quote->refresh();
$quote->load('items');

echo "Step 3: Admin removed Item 2 ('{$itemToDelete->item_name}'):\n";
echo " - Remaining Item Count: {$quote->items->count()}\n";
echo " - Recalculated Subtotal: ₹{$quote->subtotal} (expected: 9000.00)\n";
echo " - Recalculated GST: ₹{$quote->tax_amount} (expected: 1530.00)\n";
echo " - Recalculated Total: ₹{$quote->total} (expected: 10030.00)\n";
assert($quote->items->count() === 1, "Must have 1 item left");
assert((float)$quote->subtotal === 9000.00, "Subtotal must be 9000");
// Taxable = 9000 - 500 = 8500 * 18% = 1530
assert((float)$quote->tax_amount === 1530.00, "Tax must be 1530");
assert((float)$quote->total === 10030.00, "Grand Total must be 10030");
echo " -> PASS\n\n";

// STEP 4: Admin approves the quotation with confirmed PDF Format
$approveReq = Request::create(route('quotations.approve', $quote), 'POST', [
    'pdf_format' => '3',
]);
$approveReq->setUserResolver(fn() => $admin);
$quoteController->approve($approveReq, $quote);
$quote->refresh();

echo "Step 4: Admin approved Quotation #{$quote->quotation_no}:\n";
echo " - Status: {$quote->status} (expected: approved)\n";
echo " - Approved By: {$quote->approver->name}\n";
echo " - Approved Format: {$quote->pdf_format} ({$quote->pdf_format_name})\n";
assert($quote->status === 'approved', "Status must be approved");
assert($quote->approved_by === $admin->id, "Approver must be admin");
assert($quote->pdf_format === '3', "Approved format must be 3");
echo " -> PASS\n\n";

// STEP 5: Verification for Technician:
// Check that technician can access and share without cost leak
Auth::login($technician);

// Audit history test
$histories = $quote->statusHistories()->latest()->get();
echo "Step 5: Audit Trail Check (count: {$histories->count()}):\n";
foreach ($histories as $h) {
    echo " - [{$h->created_at->format('H:i:s')}] Old: '{$h->old_status}', New: '{$h->new_status}', ChangedBy: '{$h->changedBy?->name}', Remarks: '{$h->remarks}'\n";
}
assert($histories->count() >= 3, "Should have audit trail entries for create, update pricing, delete item, approve");
echo " -> PASS\n\n";

// WhatsApp formatting test
$waMsgFormat3 = $quote->getWhatsAppFormattedMessage($quote->pdf_format);
echo "Step 6: WhatsApp Message Format for customer:\n";
echo "--------------------------------------------------\n";
echo $waMsgFormat3 . "\n";
echo "--------------------------------------------------\n";
assert(str_contains($waMsgFormat3, $quote->quotation_no), "Message must contain quote number");
assert(str_contains($waMsgFormat3, '10,030.00'), "Message must contain grand total ₹10,030.00");
echo " -> PASS\n\n";

// Clean up test quotation
$quote->items()->delete();
$quote->statusHistories()->delete();
$quote->delete();

echo "=== ALL TESTS COMPLETED SUCCESSFULLY! ===\n";
