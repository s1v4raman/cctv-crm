<?php

namespace Database\Seeders;

use App\Models\AmcContract;
use App\Models\AmcVisit;
use App\Models\DailyCashReconciliation;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalary;
use App\Models\ExpenseClaim;
use App\Models\GstFiling;
use App\Models\InstallationJob;
use App\Models\InstalledEquipment;
use App\Models\Invoice;
use App\Models\JobCompletionReport;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PettyCashAccount;
use App\Models\PettyCashTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RmaClaim;
use App\Models\RmaStatusLog;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        // =========================================================================
        // 1. PRODUCTS SEEDING (Ensuring Products are up-to-date with Cost Prices)
        // =========================================================================
        $this->call(ProductSeeder::class);
        $productsBySku = Product::all()->keyBy('sku');

        // Clean up previous demo transaction/attendance tables to ensure pristine seeding
        EmployeeAttendance::where('date', '>=', $thirtyDaysAgo->toDateString())->delete();
        LeaveRequest::truncate();
        Payroll::truncate();
        PettyCashTransaction::truncate();
        DailyCashReconciliation::truncate();
        VendorPayment::truncate();
        StockMovement::truncate();
        PurchaseOrderItem::truncate();
        PurchaseOrder::truncate();
        JobCompletionReport::truncate();
        InstalledEquipment::truncate();
        Payment::truncate();
        Invoice::truncate();
        InstallationJob::truncate();
        QuotationItem::truncate();
        Quotation::truncate();
        SiteSurvey::truncate();
        AmcVisit::truncate();
        AmcContract::truncate();
        ServiceTicket::truncate();
        RmaStatusLog::truncate();
        RmaClaim::truncate();
        GstFiling::truncate();

        // Helper closures for signature SVGs
        $createSignatureSvg = function(string $name) {
            return 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="300" height="100"><text x="10" y="55" font-family="Brush Script MT, cursive" font-size="32" fill="%231e3a8a">' . htmlspecialchars($name) . '</text></svg>';
        };

        // =========================================================================
        // 2. USERS: ADMINS, STAFF & FIELD TECHNICIANS
        // =========================================================================
        $admin = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        $adminSuresh = User::updateOrCreate(
            ['email' => 'admin@cctvcrm.com'],
            [
                'name' => 'Suresh Prabhu',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        // Staff / Sales & Accounts
        $staffAlex = User::updateOrCreate(
            ['email' => 'alex@example.com'],
            [
                'name' => 'Alex Rivera',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        $staffKesavan = User::updateOrCreate(
            ['email' => 'kesavan@gmail.com'],
            [
                'name' => 'Kesavan R.',
                'password' => Hash::make('kesavan123'),
                'role' => 'staff',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        $staffPriya = User::updateOrCreate(
            ['email' => 'priya.s@example.com'],
            [
                'name' => 'Priya Sharma',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        $staffAnand = User::updateOrCreate(
            ['email' => 'anand.k@example.com'],
            [
                'name' => 'Anand Kumar',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        // Field Engineers / Technicians
        $techBob = User::updateOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Miller',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        $techRajesh = User::updateOrCreate(
            ['email' => 'rajesh.tech@example.com'],
            [
                'name' => 'Rajesh Kumar',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        $techSuresh = User::updateOrCreate(
            ['email' => 'suresh.tech@example.com'],
            [
                'name' => 'Suresh Reddy',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        $techVikram = User::updateOrCreate(
            ['email' => 'vikram.tech@example.com'],
            [
                'name' => 'Vikram Singh',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'email_verified_at' => $thirtyDaysAgo,
            ]
        );

        $allEmployees = [
            $admin, $adminSuresh,
            $staffAlex, $staffKesavan, $staffPriya, $staffAnand,
            $techBob, $techRajesh, $techSuresh, $techVikram
        ];

        $technicians = [$techBob, $techRajesh, $techSuresh, $techVikram];

        // =========================================================================
        // 3. SALARY STRUCTURES (EMPLOYEE SALARIES)
        // =========================================================================
        $salaryConfigs = [
            $admin->id => ['base' => 35000, 'daily' => 1350, 'travel' => 3000, 'special' => 2000, 'deduct' => 1500, 'method' => 'bank_transfer', 'bank' => 'HDFC Bank', 'acc' => '50100492819012', 'ifsc' => 'HDFC0001234', 'notes' => 'Admin Operations Lead'],
            $adminSuresh->id => ['base' => 45000, 'daily' => 1730, 'travel' => 4000, 'special' => 3000, 'deduct' => 2000, 'method' => 'bank_transfer', 'bank' => 'ICICI Bank', 'acc' => '002105018293', 'ifsc' => 'ICIC0000021', 'notes' => 'Managing Director'],
            $staffAlex->id => ['base' => 28000, 'daily' => 1075, 'travel' => 2500, 'special' => 1500, 'deduct' => 1000, 'method' => 'bank_transfer', 'bank' => 'State Bank of India', 'acc' => '30491829381', 'ifsc' => 'SBIN0004521', 'notes' => 'Senior Sales & Quotations Lead'],
            $staffKesavan->id => ['base' => 25000, 'daily' => 960, 'travel' => 2000, 'special' => 1000, 'deduct' => 800, 'method' => 'bank_transfer', 'bank' => 'Canara Bank', 'acc' => '119283746501', 'ifsc' => 'CNRB0001192', 'notes' => 'Sales & CRM Representative'],
            $staffPriya->id => ['base' => 24000, 'daily' => 920, 'travel' => 1500, 'special' => 1000, 'deduct' => 800, 'method' => 'bank_transfer', 'bank' => 'Axis Bank', 'acc' => '918020048192831', 'ifsc' => 'UTIB0000481', 'notes' => 'Customer Success & Scheduling'],
            $staffAnand->id => ['base' => 26000, 'daily' => 1000, 'travel' => 1500, 'special' => 1200, 'deduct' => 1000, 'method' => 'bank_transfer', 'bank' => 'HDFC Bank', 'acc' => '50100882716253', 'ifsc' => 'HDFC0001234', 'notes' => 'Accounts & GST Billing Specialist'],
            $techBob->id => ['base' => 22000, 'daily' => 850, 'travel' => 3500, 'special' => 1500, 'deduct' => 500, 'method' => 'bank_transfer', 'bank' => 'Kotak Mahindra', 'acc' => '7192830192', 'ifsc' => 'KKBK0000812', 'notes' => 'Lead CCTV Installation Engineer'],
            $techRajesh->id => ['base' => 20000, 'daily' => 770, 'travel' => 3000, 'special' => 1000, 'deduct' => 500, 'method' => 'upi', 'upi' => 'rajesh.cctv@okhdfcbank', 'notes' => 'IP Systems & Networking Tech'],
            $techSuresh->id => ['base' => 19000, 'daily' => 730, 'travel' => 3000, 'special' => 1000, 'deduct' => 500, 'method' => 'bank_transfer', 'bank' => 'Union Bank of India', 'acc' => '49281729381', 'ifsc' => 'UBIN0549281', 'notes' => 'Cabling & Rack Specialist'],
            $techVikram->id => ['base' => 21000, 'daily' => 800, 'travel' => 3200, 'special' => 1200, 'deduct' => 500, 'method' => 'upi', 'upi' => 'vikram.security@paytm', 'notes' => 'PTZ & Surveillance Field Tech'],
        ];

        foreach ($salaryConfigs as $userId => $cfg) {
            EmployeeSalary::updateOrCreate(
                ['user_id' => $userId],
                [
                    'base_salary_monthly' => $cfg['base'],
                    'daily_rate' => $cfg['daily'],
                    'weekly_rate' => round($cfg['daily'] * 6, 2),
                    'hourly_rate' => round($cfg['daily'] / 8, 2),
                    'overtime_hourly_rate' => round(($cfg['daily'] / 8) * 1.5, 2),
                    'travel_allowance' => $cfg['travel'],
                    'special_allowance' => $cfg['special'],
                    'deductions' => $cfg['deduct'],
                    'payment_method' => $cfg['method'],
                    'bank_name' => $cfg['bank'] ?? null,
                    'bank_account_number' => $cfg['acc'] ?? null,
                    'bank_ifsc' => $cfg['ifsc'] ?? null,
                    'upi_id' => $cfg['upi'] ?? null,
                    'notes' => $cfg['notes'],
                ]
            );
        }

        // =========================================================================
        // 4. ONE-MONTH ATTENDANCE RECORDS (Past 30 Days across all 10 employees)
        // =========================================================================
        for ($dayOffset = 30; $dayOffset >= 0; $dayOffset--) {
            $attDate = $now->copy()->subDays($dayOffset);
            $dayOfWeek = $attDate->dayOfWeek; // 0 = Sunday

            if ($dayOfWeek === 0) {
                // Skip Sundays (Weekly Off)
                continue;
            }

            $isSaturday = ($dayOfWeek === 6);

            foreach ($allEmployees as $index => $emp) {
                $isTechnician = in_array($emp->role, ['technician']);
                
                // Deterministic variation based on day and user
                $randVal = ($dayOffset * 7 + $emp->id * 13) % 100;

                $status = 'present';
                $clockIn = '09:05:00';
                $clockOut = $isSaturday ? '14:30:00' : '18:15:00';
                $totalHours = $isSaturday ? 5.5 : 8.5;
                $overtime = 0.0;
                $notes = 'On-time standard shift';

                if ($randVal < 4 && $dayOffset > 2) {
                    // Occasional Leave
                    $status = 'on_leave';
                    $clockIn = null;
                    $clockOut = null;
                    $totalHours = 0.0;
                    $notes = 'Approved Casual Leave';
                } elseif ($randVal < 10) {
                    // Occasional Late Arrival
                    $status = 'late';
                    $clockIn = '09:42:00';
                    $totalHours = $isSaturday ? 4.8 : 7.8;
                    $notes = 'Late due to traffic on site commute';
                } elseif ($randVal > 85 && !$isSaturday) {
                    // Overtime day (e.g. late night camera commissioning)
                    $clockOut = '20:15:00';
                    $totalHours = 10.5;
                    $overtime = 2.0;
                    $notes = 'Overtime on commercial client camera commissioning';
                }

                $locType = $isTechnician ? ($randVal % 2 == 0 ? 'on_site' : 'office') : 'office';

                EmployeeAttendance::updateOrCreate(
                    [
                        'user_id' => $emp->id,
                        'date' => $attDate->toDateString(),
                    ],
                    [
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => $status,
                        'total_hours' => $totalHours,
                        'overtime_hours' => $overtime,
                        'location_type' => $locType,
                        'notes' => $notes,
                        'marked_by' => $admin->id,
                    ]
                );
            }
        }

        // =========================================================================
        // 5. LEAVE REQUESTS
        // =========================================================================
        LeaveRequest::firstOrCreate(
            ['user_id' => $techBob->id, 'start_date' => $now->copy()->subDays(12)->toDateString()],
            [
                'end_date' => $now->copy()->subDays(11)->toDateString(),
                'leave_type' => 'casual',
                'days_count' => 2.0,
                'is_half_day' => false,
                'reason' => 'Family function and personal travel to hometown.',
                'status' => 'approved',
                'actioned_by' => $admin->id,
                'actioned_at' => $now->copy()->subDays(14),
            ]
        );

        LeaveRequest::firstOrCreate(
            ['user_id' => $staffPriya->id, 'start_date' => $now->copy()->subDays(18)->toDateString()],
            [
                'end_date' => $now->copy()->subDays(18)->toDateString(),
                'leave_type' => 'sick',
                'days_count' => 1.0,
                'is_half_day' => false,
                'reason' => 'Viral fever and doctor rest advisory.',
                'status' => 'approved',
                'actioned_by' => $admin->id,
                'actioned_at' => $now->copy()->subDays(18),
            ]
        );

        LeaveRequest::firstOrCreate(
            ['user_id' => $techRajesh->id, 'start_date' => $now->copy()->addDays(4)->toDateString()],
            [
                'end_date' => $now->copy()->addDays(5)->toDateString(),
                'leave_type' => 'casual',
                'days_count' => 2.0,
                'is_half_day' => false,
                'reason' => 'Attending brother wedding reception.',
                'status' => 'pending',
            ]
        );

        // =========================================================================
        // 6. PAYROLL RECORDS (Previous Month Paid + Current Month Draft/Approved)
        // =========================================================================
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        foreach ($allEmployees as $emp) {
            $salaryStruct = $emp->salaryStructure;
            $base = (float) ($salaryStruct?->base_salary_monthly ?? 22000);
            $allow = (float) ($salaryStruct?->travel_allowance ?? 2000) + (float) ($salaryStruct?->special_allowance ?? 1000);
            $ded = (float) ($salaryStruct?->deductions ?? 500);
            $net = $base + $allow - $ded;

            // Last Month Paid Payroll
            $payrollNoLast = 'PAY-' . $lastMonthStart->format('Ym') . '-' . str_pad((string) $emp->id, 3, '0', STR_PAD_LEFT);
            Payroll::firstOrCreate(
                ['payroll_number' => $payrollNoLast],
                [
                    'user_id' => $emp->id,
                    'period_type' => 'monthly',
                    'period_start' => $lastMonthStart->toDateString(),
                    'period_end' => $lastMonthEnd->toDateString(),
                    'working_days' => 26,
                    'present_days' => 25.0,
                    'half_days' => 1,
                    'leave_days' => 1.0,
                    'absent_days' => 0.0,
                    'overtime_hours' => 6.0,
                    'basic_pay' => $base,
                    'overtime_pay' => 1200.00,
                    'allowances' => $allow,
                    'deductions' => $ded,
                    'net_salary' => $net + 1200.00,
                    'status' => 'paid',
                    'payment_date' => $now->copy()->subDays(20)->toDateString(),
                    'payment_reference' => 'NEFT-SAL-' . $lastMonthStart->format('Ym') . '-' . rand(10000, 99999),
                    'notes' => 'Processed and credited via HDFC Corporate Direct NetBanking.',
                    'created_by' => $admin->id,
                ]
            );

            // Current Month Approved Payroll
            $payrollNoCurr = 'PAY-' . $startOfMonth->format('Ym') . '-' . str_pad((string) $emp->id, 3, '0', STR_PAD_LEFT);
            Payroll::firstOrCreate(
                ['payroll_number' => $payrollNoCurr],
                [
                    'user_id' => $emp->id,
                    'period_type' => 'monthly',
                    'period_start' => $startOfMonth->toDateString(),
                    'period_end' => $now->copy()->endOfMonth()->toDateString(),
                    'working_days' => 26,
                    'present_days' => 21.0,
                    'half_days' => 0,
                    'leave_days' => 1.0,
                    'absent_days' => 0.0,
                    'overtime_hours' => 4.0,
                    'basic_pay' => $base,
                    'overtime_pay' => 800.00,
                    'allowances' => $allow,
                    'deductions' => $ded,
                    'net_salary' => $net + 800.00,
                    'status' => 'approved',
                    'notes' => 'Current running payroll cycle ready for month-end payout.',
                    'created_by' => $admin->id,
                ]
            );
        }

        // =========================================================================
        // 7. PETTY CASH ACCOUNTS & TRANSACTIONS
        // =========================================================================
        $mainVault = PettyCashAccount::getMainVault();
        $mainVault->update(['current_balance' => 38500.00]);

        $walletBob = PettyCashAccount::getOrCreateWalletForUser($techBob);
        $walletBob->update(['current_balance' => 3200.00]);

        $walletRajesh = PettyCashAccount::getOrCreateWalletForUser($techRajesh);
        $walletRajesh->update(['current_balance' => 2450.00]);

        // Seed transactions for Petty Cash
        PettyCashTransaction::firstOrCreate(
            ['voucher_no' => 'PCV-20260901-0001'],
            [
                'petty_cash_account_id' => $mainVault->id,
                'destination_account_id' => $walletBob->id,
                'user_id' => $admin->id,
                'transaction_type' => 'float_advance',
                'amount' => 5000.00,
                'transaction_date' => $now->copy()->subDays(24)->toDateString(),
                'category' => 'Field Float Disbursement',
                'notes' => 'Field wallet advance float for Bob Miller (Bangalore East projects)',
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => $now->copy()->subDays(24),
            ]
        );

        PettyCashTransaction::firstOrCreate(
            ['voucher_no' => 'PCV-20260904-0002'],
            [
                'petty_cash_account_id' => $walletBob->id,
                'user_id' => $techBob->id,
                'transaction_type' => 'direct_expense',
                'amount' => 650.00,
                'transaction_date' => $now->copy()->subDays(21)->toDateString(),
                'category' => 'Tools & Site Hardware',
                'vendor_payee_name' => 'Sri Balaji Electricals & Hardware',
                'notes' => 'Purchased PVC flexible conduit pipes and heavy-duty anchor screws locally at site.',
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => $now->copy()->subDays(21),
            ]
        );

        PettyCashTransaction::firstOrCreate(
            ['voucher_no' => 'PCV-20260910-0003'],
            [
                'petty_cash_account_id' => $walletBob->id,
                'user_id' => $techBob->id,
                'transaction_type' => 'direct_expense',
                'amount' => 450.00,
                'transaction_date' => $now->copy()->subDays(15)->toDateString(),
                'category' => 'Food & Refreshments',
                'vendor_payee_name' => 'Cafeteria / Local Store',
                'notes' => 'Technician team site refreshments and water for high-rise tower installation.',
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => $now->copy()->subDays(15),
            ]
        );

        // Daily Cash Reconciliation
        DailyCashReconciliation::firstOrCreate(
            ['petty_cash_account_id' => $mainVault->id, 'reconciliation_date' => $now->copy()->subDays(1)->toDateString()],
            [
                'reconciliation_no' => 'REC-CASH-' . $now->format('Ymd') . '-0001',
                'custodian_id' => $admin->id,
                'opening_balance' => 35000.00,
                'total_inflow' => 5000.00,
                'total_outflow' => 1500.00,
                'system_expected_balance' => 38500.00,
                'physical_counted_balance' => 38500.00,
                'variance_amount' => 0.00,
                'variance_status' => 'matched',
                'denominations' => ['500' => 70, '200' => 15, '100' => 5],
                'reconciliation_notes' => 'Main office cash vault matched perfectly with all vouchers on physical count.',
                'verified_by' => $admin->id,
                'verified_at' => $now->copy()->subDays(1),
                'status' => 'verified',
            ]
        );

        // =========================================================================
        // 8. SUPPLIERS, PURCHASE ORDERS, VENDOR PAYMENTS & STOCK
        // =========================================================================
        $supHikvision = Supplier::updateOrCreate(
            ['email' => 'sales@hikvision-distributor.in'],
            [
                'name' => 'Hikvision Authorized National Distributor',
                'company_name' => 'Prama Hikvision India Pvt Ltd',
                'contact_person' => 'Rohan Varma',
                'phone' => '+91 98450 11223',
                'gst_number' => '29AABCP1928K1ZB',
                'address' => 'Industrial Area, Peenya 2nd Stage',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'payment_terms' => 'Net 30 Days',
                'is_active' => true,
                'notes' => 'Primary distributor for IP Cameras, 4K NVRs, and ColorVu series.',
            ]
        );

        $supDahua = Supplier::updateOrCreate(
            ['email' => 'orders@dahua-impex.in'],
            [
                'name' => 'Dahua Technology India Direct',
                'company_name' => 'Dahua Surveillance Impex',
                'contact_person' => 'Meenakshi Iyer',
                'phone' => '+91 98451 99887',
                'gst_number' => '29AABCD4491J1ZT',
                'address' => 'Electronic City Phase 1',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'payment_terms' => 'Net 15 Days',
                'is_active' => true,
            ]
        );

        $supWD = Supplier::updateOrCreate(
            ['email' => 'distribution@wd-storage.in'],
            [
                'name' => 'Western Digital & Seagate Distribution Hub',
                'company_name' => 'TechnoSource Storage India',
                'contact_person' => 'Karthik Raman',
                'phone' => '+91 98455 33445',
                'gst_number' => '29AABCT8812D1ZQ',
                'address' => 'SP Road Electronics Market',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'payment_terms' => 'Immediate / Advance',
                'is_active' => true,
            ]
        );

        $supPolycab = Supplier::updateOrCreate(
            ['email' => 'orders@polycab-wires.in'],
            [
                'name' => 'Polycab & D-Link Networking Depot',
                'company_name' => 'Infra Cables & Switchgear LLP',
                'contact_person' => 'Girish Hegde',
                'phone' => '+91 98452 77665',
                'gst_number' => '29AABCI5541A1ZM',
                'address' => 'BTM Layout 2nd Stage',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'payment_terms' => 'Net 30 Days',
                'is_active' => true,
            ]
        );

        // Purchase Orders across past 30 days
        $poList = [
            [
                'po_number' => 'PO-2026-081',
                'supplier' => $supHikvision,
                'date' => $now->copy()->subDays(28),
                'status' => 'received',
                'pay_status' => 'paid',
                'items' => [
                    ['sku' => 'HK-4MP-BULLET', 'qty' => 30, 'price' => 2100],
                    ['sku' => 'HK-4MP-DOME', 'qty' => 30, 'price' => 2200],
                    ['sku' => 'NVR-8CH', 'qty' => 8, 'price' => 5300],
                    ['sku' => 'NVR-16CH', 'qty' => 5, 'price' => 7900],
                ],
                'shipping' => 1500,
                'notes' => 'Bulk monthly replenishment for August/September project rollouts.',
            ],
            [
                'po_number' => 'PO-2026-082',
                'supplier' => $supWD,
                'date' => $now->copy()->subDays(25),
                'status' => 'received',
                'pay_status' => 'paid',
                'items' => [
                    ['sku' => 'HDD-SURV-2TB', 'qty' => 25, 'price' => 4300],
                    ['sku' => 'HDD-SURV-4TB', 'qty' => 15, 'price' => 6800],
                    ['sku' => 'HDD-SURV-6TB', 'qty' => 6, 'price' => 10200],
                ],
                'shipping' => 800,
                'notes' => 'Surveillance Purple hard drives batch procurement.',
            ],
            [
                'po_number' => 'PO-2026-083',
                'supplier' => $supPolycab,
                'date' => $now->copy()->subDays(20),
                'status' => 'received',
                'pay_status' => 'paid',
                'items' => [
                    ['sku' => 'CABLE-CAT6', 'qty' => 2500, 'price' => 12],
                    ['sku' => 'POE-8PORT', 'qty' => 12, 'price' => 3500],
                    ['sku' => 'POE-16PORT', 'qty' => 8, 'price' => 6800],
                    ['sku' => 'RACK-WALL-6U', 'qty' => 8, 'price' => 2300],
                ],
                'shipping' => 1200,
                'notes' => 'Structured cabling and PoE infrastructure batch.',
            ],
            [
                'po_number' => 'PO-2026-084',
                'supplier' => $supHikvision,
                'date' => $now->copy()->subDays(12),
                'status' => 'received',
                'pay_status' => 'partially_paid',
                'items' => [
                    ['sku' => 'HK-5MP-COLORVU', 'qty' => 20, 'price' => 3100],
                    ['sku' => 'HK-2MP-PTZ', 'qty' => 4, 'price' => 11500],
                    ['sku' => 'NVR-32CH', 'qty' => 3, 'price' => 15500],
                ],
                'shipping' => 1800,
                'notes' => 'High-end ColorVu and PTZ cameras for commercial & jewellery projects.',
            ],
            [
                'po_number' => 'PO-2026-085',
                'supplier' => $supDahua,
                'date' => $now->copy()->subDays(5),
                'status' => 'received',
                'pay_status' => 'unpaid',
                'items' => [
                    ['sku' => 'DAH-4MP-IP-BULLET', 'qty' => 20, 'price' => 2050],
                    ['sku' => 'TPLINK-SW-8G', 'qty' => 15, 'price' => 1550],
                    ['sku' => 'TPLINK-ROUTER-GIG', 'qty' => 10, 'price' => 1800],
                ],
                'shipping' => 950,
                'notes' => 'Network switches and IP bullets dispatch.',
            ],
        ];

        foreach ($poList as $poData) {
            $subtotal = 0.0;
            foreach ($poData['items'] as $item) {
                $subtotal += ($item['qty'] * $item['price']);
            }
            $taxAmount = round($subtotal * 0.18, 2);
            $total = $subtotal + $taxAmount + $poData['shipping'];

            $paidAmount = 0.0;
            if ($poData['pay_status'] === 'paid') {
                $paidAmount = $total;
            } elseif ($poData['pay_status'] === 'partially_paid') {
                $paidAmount = round($total * 0.5, 2);
            }

            $po = PurchaseOrder::updateOrCreate(
                ['po_number' => $poData['po_number']],
                [
                    'supplier_id' => $poData['supplier']->id,
                    'order_date' => $poData['date']->toDateString(),
                    'expected_delivery_date' => $poData['date']->copy()->addDays(3)->toDateString(),
                    'due_date' => $poData['date']->copy()->addDays(30)->toDateString(),
                    'status' => $poData['status'],
                    'subtotal' => $subtotal,
                    'tax_percent' => 18.00,
                    'tax_amount' => $taxAmount,
                    'shipping_cost' => $poData['shipping'],
                    'total' => $total,
                    'payment_status' => $poData['pay_status'],
                    'amount_paid' => $paidAmount,
                    'notes' => $poData['notes'],
                    'created_by' => $admin->id,
                ]
            );

            // Purchase Order Items
            foreach ($poData['items'] as $item) {
                $prod = $productsBySku[$item['sku']] ?? null;
                PurchaseOrderItem::updateOrCreate(
                    [
                        'purchase_order_id' => $po->id,
                        'product_id' => $prod?->id,
                        'sku' => $item['sku'],
                    ],
                    [
                        'item_name' => $prod?->name ?? $item['sku'],
                        'unit_cost' => $item['price'],
                        'quantity_ordered' => $item['qty'],
                        'quantity_received' => $item['qty'],
                        'total_cost' => round($item['qty'] * $item['price'], 2),
                    ]
                );

                // Log Stock Movement
                if ($prod) {
                    StockMovement::firstOrCreate(
                        [
                            'product_id' => $prod->id,
                            'reference_type' => PurchaseOrder::class,
                            'reference_id' => $po->id,
                        ],
                        [
                            'type' => 'in',
                            'quantity' => $item['qty'],
                            'balance_after' => $prod->stock_quantity,
                            'notes' => "Received from {$poData['supplier']->name} under {$po->po_number}",
                            'user_id' => $admin->id,
                        ]
                    );
                }
            }

            // Vendor Payment if paid / partially paid
            if ($paidAmount > 0) {
                VendorPayment::firstOrCreate(
                    ['purchase_order_id' => $po->id, 'payment_reference' => 'VPAY-' . $po->po_number],
                    [
                        'supplier_id' => $poData['supplier']->id,
                        'payment_date' => $poData['date']->copy()->addDays(5)->toDateString(),
                        'amount' => $paidAmount,
                        'payment_method' => 'bank_transfer',
                        'transaction_reference' => 'RTGS/HDFC/VENDOR/' . rand(100000, 999999),
                        'notes' => "Procurement settlement for {$po->po_number}",
                        'created_by' => $admin->id,
                    ]
                );
            }
        }

        // =========================================================================
        // 9. REALISTIC CUSTOMER BUYING & PROJECT FLOW DATA (PAST 30 DAYS)
        // =========================================================================
        
        $customerProjects = [
            // Project 1: Residential Villa (Srinithish P)
            [
                'lead' => [
                    'name' => 'Srinithish P',
                    'email' => 'srinithish.p@example.com',
                    'phone' => '+91 98765 43210',
                    'address' => '74/2 Cyber Tech Residency, Electronic City, Bangalore',
                    'status' => 'won',
                    'source' => 'Website Referral',
                    'created_offset' => 26,
                    'won_offset' => 24,
                    'notes' => 'High-priority luxury villa 8-Camera 4K CCTV surveillance system with 1-Year AMC contract.',
                ],
                'survey' => [
                    'tech' => $techBob,
                    'offset' => 25,
                    'cams' => 8,
                    'cable' => 180,
                    'dvr_loc' => 'Ground Floor Server Rack',
                    'power' => 'Dedicated 1kVA UPS Available',
                    'notes' => 'Site survey completed. Perimeter coverage approved by client.',
                ],
                'quote' => [
                    'no' => 'QT-2026-SRI01',
                    'offset' => 24,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-4MP-BULLET', 'qty' => 4],
                        ['sku' => 'HK-4MP-DOME', 'qty' => 4],
                        ['sku' => 'NVR-8CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-2TB', 'qty' => 1],
                        ['sku' => 'POE-8PORT', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 180],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-SRI01',
                    'tech' => $techBob,
                    'sched_offset' => 22,
                    'done_offset' => 21,
                    'status' => 'completed',
                    'labor_hours' => 12.5,
                    'custom_rate' => 250,
                    'direct_costs' => 800,
                    'cost_notes' => 'Trunking casing and conduit fitting.',
                    'notes' => 'All 8 cameras mounted and calibrated. Mobile app access configured.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0089',
                    'signer_name' => 'Srinithish P',
                    'signer_desig' => 'Property Owner',
                    'signer_phone' => '+91 98765 43210',
                    'rating' => 5,
                    'feedback' => 'Excellent neat installation and crystal clear camera streaming on phones.',
                    'summary' => 'Installed 8x 4MP IP Cameras with ColorVu night vision, 8-Ch NVR & Cat6 Cabling.',
                ],
                'equipment' => [
                    ['sku' => 'HK-4MP-BULLET', 'name' => 'Hikvision 4MP IP Bullet Camera', 'serial' => 'HKV-4MP-984210', 'mac' => 'BC:92:68:5A:11:01', 'loc' => 'Main Front Gate & Porch', 'mfg_m' => 24, 'srv_m' => 12],
                    ['sku' => 'HK-4MP-DOME', 'name' => 'Hikvision 4MP IP Dome Camera', 'serial' => 'HKV-4MP-984211', 'mac' => 'BC:92:68:5A:11:02', 'loc' => 'Living Hall & Foyer', 'mfg_m' => 24, 'srv_m' => 12],
                    ['sku' => 'NVR-8CH', 'name' => 'Hikvision 8-Channel 4K NVR with 2TB HDD', 'serial' => 'NVR-8CH-4K-55219', 'mac' => 'BC:92:68:5A:11:99', 'loc' => 'Ground Floor IT Rack', 'mfg_m' => 36, 'srv_m' => 12],
                ],
                'amc' => [
                    'no' => 'AMC-2026-SRI',
                    'freq' => 'quarterly',
                    'value' => 12000,
                    'start_offset' => 21,
                ],
                'invoice' => [
                    'no' => 'INV-2026-0042',
                    'offset' => 21,
                    'status' => 'paid',
                    'pay_offset' => 20,
                    'pay_method' => 'upi',
                    'pay_ref' => 'UPI/RAZORPAY/PAY_9841829',
                ],
            ],

            // Project 2: Commercial IT Tech Park (Anita Desai - TechPark Solutions)
            [
                'lead' => [
                    'name' => 'Anita Desai',
                    'company' => 'TechPark Solutions Pvt Ltd',
                    'email' => 'anita.desai@techpark-corp.com',
                    'phone' => '+91 98450 77123',
                    'address' => 'Tower B, Global Tech Park, Outer Ring Road, Bellandur, Bangalore',
                    'status' => 'won',
                    'source' => 'Corporate Exhibition',
                    'created_offset' => 28,
                    'won_offset' => 26,
                    'notes' => '16-Camera enterprise security installation with server room monitoring and AMC.',
                ],
                'survey' => [
                    'tech' => $techRajesh,
                    'offset' => 27,
                    'cams' => 16,
                    'cable' => 450,
                    'dvr_loc' => '3rd Floor Server Room Rack 2',
                    'power' => 'Centralised 10kVA Online UPS',
                    'notes' => 'Survey completed with Facility Director. 16 ColorVu cameras recommended.',
                ],
                'quote' => [
                    'no' => 'QT-2026-TP02',
                    'offset' => 26,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-5MP-COLORVU', 'qty' => 16],
                        ['sku' => 'NVR-16CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-6TB', 'qty' => 1],
                        ['sku' => 'POE-16PORT', 'qty' => 1],
                        ['sku' => 'RACK-WALL-9U', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 450],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                        ['sku' => 'SERVICE-RACK-INSTALL', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-TP02',
                    'tech' => $techRajesh,
                    'sched_offset' => 23,
                    'done_offset' => 20,
                    'status' => 'completed',
                    'labor_hours' => 26.0,
                    'custom_rate' => 300,
                    'direct_costs' => 2200,
                    'cost_notes' => 'Cable trays, ceiling tiles access equipment and heavy-duty patch cords.',
                    'notes' => '16x 5MP ColorVu cameras successfully integrated into Milestone VMS server.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0090',
                    'signer_name' => 'Anita Desai',
                    'signer_desig' => 'Head of Infrastructure',
                    'signer_phone' => '+91 98450 77123',
                    'rating' => 5,
                    'feedback' => 'Flawless execution, zero downtime during business hours, top notch cabling.',
                    'summary' => 'Commissioned 16 ColorVu 5MP IP Cameras with 16-Port PoE switch & 9U Rack.',
                ],
                'equipment' => [
                    ['sku' => 'HK-5MP-COLORVU', 'name' => 'Hikvision 5MP ColorVu Bullet Camera', 'serial' => 'HKV-5MP-CV-882190', 'mac' => 'BC:92:68:5A:22:01', 'loc' => 'Office Floor 3 Main Entrance', 'mfg_m' => 24, 'srv_m' => 12],
                    ['sku' => 'NVR-16CH', 'name' => 'Hikvision 16-Channel 4K NVR', 'serial' => 'NVR-16CH-4K-99012', 'mac' => 'BC:92:68:5A:22:99', 'loc' => 'Server Room Rack 2', 'mfg_m' => 36, 'srv_m' => 12],
                ],
                'amc' => [
                    'no' => 'AMC-2026-TP02',
                    'freq' => 'quarterly',
                    'value' => 24000,
                    'start_offset' => 20,
                ],
                'invoice' => [
                    'no' => 'INV-2026-0043',
                    'offset' => 20,
                    'status' => 'paid',
                    'pay_offset' => 18,
                    'pay_method' => 'bank_transfer',
                    'pay_ref' => 'NEFT/HDFC/TECHPARK/901283',
                ],
            ],

            // Project 3: Retail Supermarket Chain (Ramesh Patel - Green Valley Supermarket)
            [
                'lead' => [
                    'name' => 'Ramesh Patel',
                    'company' => 'Green Valley Supermarkets',
                    'email' => 'ramesh@greenvalleysuper.com',
                    'phone' => '+91 98760 11992',
                    'address' => 'Plot 12, Indiranagar 100ft Road, Bangalore',
                    'status' => 'won',
                    'source' => 'Direct Walk-in / Inbound',
                    'created_offset' => 23,
                    'won_offset' => 21,
                    'notes' => '12-Camera Dome system for retail checkout aisles and cash counters.',
                ],
                'survey' => [
                    'tech' => $techSuresh,
                    'offset' => 22,
                    'cams' => 12,
                    'cable' => 300,
                    'dvr_loc' => 'Store Manager Office',
                    'power' => 'Store UPS with 2hr backup',
                    'notes' => 'Survey completed. 12 indoor dome cameras positioned over billing counters.',
                ],
                'quote' => [
                    'no' => 'QT-2026-GV03',
                    'offset' => 21,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-4MP-DOME', 'qty' => 12],
                        ['sku' => 'NVR-16CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-4TB', 'qty' => 1],
                        ['sku' => 'POE-16PORT', 'qty' => 1],
                        ['sku' => 'RACK-WALL-6U', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 300],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-GV03',
                    'tech' => $techSuresh,
                    'sched_offset' => 19,
                    'done_offset' => 17,
                    'status' => 'completed',
                    'labor_hours' => 18.0,
                    'custom_rate' => 260,
                    'direct_costs' => 1100,
                    'cost_notes' => 'False ceiling routing clips and conduit pipes.',
                    'notes' => 'All 12 dome cameras tested with face detection across cash counters.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0091',
                    'signer_name' => 'Ramesh Patel',
                    'signer_desig' => 'Store Proprietor',
                    'signer_phone' => '+91 98760 11992',
                    'rating' => 4,
                    'feedback' => 'Good coverage of cash counters and customer billing lanes.',
                    'summary' => 'Installed 12x 4MP Dome IP Cameras & 16-Channel NVR.',
                ],
                'equipment' => [
                    ['sku' => 'HK-4MP-DOME', 'name' => 'Hikvision 4MP Dome Camera', 'serial' => 'HKV-4MP-DOM-11029', 'mac' => 'BC:92:68:5A:33:01', 'loc' => 'Cash Billing Counter 1 & 2', 'mfg_m' => 24, 'srv_m' => 12],
                ],
                'amc' => [
                    'no' => 'AMC-2026-GV03',
                    'freq' => 'quarterly',
                    'value' => 15000,
                    'start_offset' => 17,
                ],
                'invoice' => [
                    'no' => 'INV-2026-0044',
                    'offset' => 17,
                    'status' => 'partially_paid',
                    'pay_offset' => 16,
                    'pay_method' => 'cheque',
                    'pay_ref' => 'CHQ-HDFC-918273',
                    'advance_pct' => 0.60, // 60% paid, 40% balance due for receivables aging
                ],
            ],

            // Project 4: High Security Jewellery Showroom (K. Sundaram - Sri Lakshmi Jewellers)
            [
                'lead' => [
                    'name' => 'K. Sundaram',
                    'company' => 'Sri Lakshmi Jewellers & Diamond Merchants',
                    'email' => 'sundaram@lakshmijewels.in',
                    'phone' => '+91 98440 22334',
                    'address' => 'Commercial Street, Shivaji Nagar, Bangalore',
                    'status' => 'won',
                    'source' => 'Client Referral',
                    'created_offset' => 21,
                    'won_offset' => 19,
                    'notes' => 'Ultra high-security 4K PTZ and ColorVu CCTV system with safe locker monitoring.',
                ],
                'survey' => [
                    'tech' => $techVikram,
                    'offset' => 20,
                    'cams' => 14,
                    'cable' => 380,
                    'dvr_loc' => 'Underground Secure Vault Room',
                    'power' => 'Dual UPS with Generator Failover',
                    'notes' => 'High security audit. Recommended PTZ optical zoom cameras for gold appraisal counter.',
                ],
                'quote' => [
                    'no' => 'QT-2026-SLJ04',
                    'offset' => 19,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-2MP-PTZ', 'qty' => 4],
                        ['sku' => 'HK-5MP-COLORVU', 'qty' => 8],
                        ['sku' => 'NVR-32CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-6TB', 'qty' => 2],
                        ['sku' => 'POE-24PORT-GIG', 'qty' => 1],
                        ['sku' => 'RACK-WALL-9U', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 380],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                        ['sku' => 'SERVICE-RACK-INSTALL', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-SLJ04',
                    'tech' => $techVikram,
                    'sched_offset' => 16,
                    'done_offset' => 14,
                    'status' => 'completed',
                    'labor_hours' => 30.0,
                    'custom_rate' => 320,
                    'direct_costs' => 2800,
                    'cost_notes' => 'Armored metal conduits and tamper-proof security junction boxes.',
                    'notes' => '4x PTZ cameras calibrated with preset patrol positions covering diamond display counters.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0092',
                    'signer_name' => 'K. Sundaram',
                    'signer_desig' => 'Managing Partner',
                    'signer_phone' => '+91 98440 22334',
                    'rating' => 5,
                    'feedback' => 'Exceptional optical clarity on jewellery items and swift response time.',
                    'summary' => 'Installed 4x PTZ Cameras, 8x ColorVu 5MP IP Cameras, 32-Ch NVR & 24-Port Gigabit PoE.',
                ],
                'equipment' => [
                    ['sku' => 'HK-2MP-PTZ', 'name' => 'Hikvision 2MP PTZ 25x Speed Dome', 'serial' => 'HKV-PTZ-25X-90182', 'mac' => 'BC:92:68:5A:44:01', 'loc' => 'Diamond Display Counter & Vault Door', 'mfg_m' => 36, 'srv_m' => 12],
                    ['sku' => 'NVR-32CH', 'name' => 'Hikvision 32-Channel 4K Enterprise NVR', 'serial' => 'NVR-32CH-4K-88129', 'mac' => 'BC:92:68:5A:44:99', 'loc' => 'Underground Secure Vault Room', 'mfg_m' => 36, 'srv_m' => 12],
                ],
                'amc' => [
                    'no' => 'AMC-2026-SLJ04',
                    'freq' => 'monthly',
                    'value' => 36000,
                    'start_offset' => 14,
                ],
                'invoice' => [
                    'no' => 'INV-2026-0045',
                    'offset' => 14,
                    'status' => 'paid',
                    'pay_offset' => 13,
                    'pay_method' => 'bank_transfer',
                    'pay_ref' => 'RTGS/AXIS/JEWELLERY/881920',
                ],
            ],

            // Project 5: Industrial Logistics Warehouse (Manoj Kumar - Apex Logistics)
            [
                'lead' => [
                    'name' => 'Manoj Kumar',
                    'company' => 'Apex Logistics & Freight Hub',
                    'email' => 'manoj.k@apexlogistics.in',
                    'phone' => '+91 98453 88192',
                    'address' => 'Warehouse Complex 7, Nelamangala Industrial Corridor, Bangalore',
                    'status' => 'won',
                    'source' => 'Website Referral',
                    'created_offset' => 19,
                    'won_offset' => 17,
                    'notes' => '12-Camera outdoor IP bullet surveillance system covering loading bays and truck gates.',
                ],
                'survey' => [
                    'tech' => $techBob,
                    'offset' => 18,
                    'cams' => 12,
                    'cable' => 520,
                    'dvr_loc' => 'Security Guard Main Gate Cabin',
                    'power' => 'Solar UPS system',
                    'notes' => 'Heavy vehicle movement area. Long range IR bullets required.',
                ],
                'quote' => [
                    'no' => 'QT-2026-AL05',
                    'offset' => 17,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-4MP-BULLET', 'qty' => 10],
                        ['sku' => 'HK-2MP-PTZ', 'qty' => 2],
                        ['sku' => 'NVR-16CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-4TB', 'qty' => 1],
                        ['sku' => 'POE-16PORT', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 520],
                        ['sku' => 'UPS-1KVA', 'qty' => 1],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-AL05',
                    'tech' => $techBob,
                    'sched_offset' => 14,
                    'done_offset' => 12,
                    'status' => 'completed',
                    'labor_hours' => 22.0,
                    'custom_rate' => 280,
                    'direct_costs' => 1800,
                    'cost_notes' => 'Heavy clamp mounting poles for perimeter boundary walls.',
                    'notes' => 'Truck number plate recognition cameras tuned and tested at Gate 1 & 2.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0093',
                    'signer_name' => 'Manoj Kumar',
                    'signer_desig' => 'Operations GM',
                    'signer_phone' => '+91 98453 88192',
                    'rating' => 5,
                    'feedback' => 'Night vision across loading bays is outstanding. Very professional job.',
                    'summary' => 'Installed 10x IP Bullets, 2x PTZ, 16-Channel NVR & Industrial Surge UPS.',
                ],
                'equipment' => [
                    ['sku' => 'HK-4MP-BULLET', 'name' => 'Hikvision 4MP IP Bullet Camera', 'serial' => 'HKV-4MP-LOG-90182', 'mac' => 'BC:92:68:5A:55:01', 'loc' => 'Loading Bay Dock 1 to 4', 'mfg_m' => 24, 'srv_m' => 12],
                ],
                'amc' => [
                    'no' => 'AMC-2026-AL05',
                    'freq' => 'quarterly',
                    'value' => 18000,
                    'start_offset' => 12,
                ],
                'invoice' => [
                    'no' => 'INV-2026-0046',
                    'offset' => 12,
                    'status' => 'paid',
                    'pay_offset' => 10,
                    'pay_method' => 'bank_transfer',
                    'pay_ref' => 'NEFT/ICICI/APEX/990182',
                ],
            ],

            // Project 6: Gated Community HOA (Divya Nair - Blue Horizon Heights) - In Progress
            [
                'lead' => [
                    'name' => 'Divya Nair',
                    'company' => 'Blue Horizon Apartments Association',
                    'email' => 'secretary@bluehorizonapartments.org',
                    'phone' => '+91 98451 44556',
                    'address' => 'Survey 48, Bannerghatta Main Road, Bangalore',
                    'status' => 'won',
                    'source' => 'Referral',
                    'created_offset' => 15,
                    'won_offset' => 13,
                    'notes' => '24-Camera society campus security upgrading analog to IP ColorVu surveillance.',
                ],
                'survey' => [
                    'tech' => $techRajesh,
                    'offset' => 14,
                    'cams' => 24,
                    'cable' => 750,
                    'dvr_loc' => 'Clubhouse Security Control Room',
                    'power' => 'Centralised DG backup',
                    'notes' => 'Society AGM approved 24 cameras project. Underground pipe trenching required.',
                ],
                'quote' => [
                    'no' => 'QT-2026-BH06',
                    'offset' => 13,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-4MP-BULLET', 'qty' => 16],
                        ['sku' => 'HK-4MP-DOME', 'qty' => 8],
                        ['sku' => 'NVR-32CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-6TB', 'qty' => 2],
                        ['sku' => 'POE-24PORT-GIG', 'qty' => 1],
                        ['sku' => 'RACK-WALL-9U', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 750],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                        ['sku' => 'SERVICE-RACK-INSTALL', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-BH06',
                    'tech' => $techRajesh,
                    'sched_offset' => 8,
                    'done_offset' => null,
                    'status' => 'in_progress', // Active In Progress Job
                    'labor_hours' => 16.0,
                    'custom_rate' => 280,
                    'direct_costs' => 1400,
                    'cost_notes' => 'Underground conduit laying and tower block distribution.',
                    'notes' => 'Block A & B cabling completed. Block C and clubhouse remaining.',
                ],
                'invoice' => [
                    'no' => 'INV-2026-0047',
                    'offset' => 8,
                    'status' => 'partially_paid',
                    'pay_offset' => 7,
                    'pay_method' => 'bank_transfer',
                    'pay_ref' => 'NEFT/SBI/HOA/491823',
                    'advance_pct' => 0.50, // 50% mobilization advance paid
                ],
            ],

            // Project 7: Healthcare Clinic (Dr. Farhan Ali - Metro Diagnostics)
            [
                'lead' => [
                    'name' => 'Dr. Farhan Ali',
                    'company' => 'Metro Diagnostic & Pathology Lab',
                    'email' => 'dr.farhan@metrodiag.com',
                    'phone' => '+91 98456 77889',
                    'address' => '22/1 CMH Road, Indiranagar, Bangalore',
                    'status' => 'won',
                    'source' => 'Direct Walk-in',
                    'created_offset' => 16,
                    'won_offset' => 14,
                    'notes' => '8-Camera system for patient registration, sample collection and pharmacy counters.',
                ],
                'survey' => [
                    'tech' => $techVikram,
                    'offset' => 15,
                    'cams' => 8,
                    'cable' => 160,
                    'dvr_loc' => 'Chief Doctor Consultation Chamber',
                    'power' => 'Inverter Power',
                    'notes' => 'Clean aesthetic wiring required across clinical zones.',
                ],
                'quote' => [
                    'no' => 'QT-2026-MD07',
                    'offset' => 14,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-4MP-DOME', 'qty' => 8],
                        ['sku' => 'NVR-8CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-2TB', 'qty' => 1],
                        ['sku' => 'POE-8PORT', 'qty' => 1],
                        ['sku' => 'RACK-WALL-6U', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 160],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-MD07',
                    'tech' => $techVikram,
                    'sched_offset' => 11,
                    'done_offset' => 10,
                    'status' => 'completed',
                    'labor_hours' => 10.0,
                    'custom_rate' => 250,
                    'direct_costs' => 600,
                    'cost_notes' => 'Concealed ceiling conduits.',
                    'notes' => 'All 8 dome cameras active with dual mobile app access on doctor iPad & phone.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0094',
                    'signer_name' => 'Dr. Farhan Ali',
                    'signer_desig' => 'Managing Director',
                    'signer_phone' => '+91 98456 77889',
                    'rating' => 5,
                    'feedback' => 'Silent and tidy work during clinical hours. Highly recommended.',
                    'summary' => 'Installed 8x 4MP Dome Cameras with 8-Ch NVR & Remote Access.',
                ],
                'equipment' => [
                    ['sku' => 'HK-4MP-DOME', 'name' => 'Hikvision 4MP Dome Camera', 'serial' => 'HKV-4MP-CLI-88192', 'mac' => 'BC:92:68:5A:66:01', 'loc' => 'Pathology Sample Collection Desk', 'mfg_m' => 24, 'srv_m' => 12],
                ],
                'amc' => [
                    'no' => 'AMC-2026-MD07',
                    'freq' => 'annually',
                    'value' => 14000,
                    'start_offset' => 10,
                ],
                'invoice' => [
                    'no' => 'INV-2026-0048',
                    'offset' => 10,
                    'status' => 'paid',
                    'pay_offset' => 9,
                    'pay_method' => 'upi',
                    'pay_ref' => 'UPI/GPay/DRFARHAN/89123',
                ],
            ],

            // Project 8: Modern Co-Working Space (Vikramaditya - Nexus Hub) - Overdue Invoice for Aging Analysis
            [
                'lead' => [
                    'name' => 'Vikramaditya',
                    'company' => 'Nexus Co-Working & Incubation Hub',
                    'email' => 'admin@nexuscowork.space',
                    'phone' => '+91 98458 99001',
                    'address' => 'Level 4, Prestige Meridian, MG Road, Bangalore',
                    'status' => 'won',
                    'source' => 'Website Inquiry',
                    'created_offset' => 27,
                    'won_offset' => 25,
                    'notes' => '10-Camera Wi-Fi and IP combo system for shared desks and private meeting cabins.',
                ],
                'survey' => [
                    'tech' => $techSuresh,
                    'offset' => 26,
                    'cams' => 10,
                    'cable' => 220,
                    'dvr_loc' => 'IT Server Rack',
                    'power' => 'Building UPS',
                    'notes' => 'Recommended combination of Wi-Fi PT and fixed IP domes for conference rooms.',
                ],
                'quote' => [
                    'no' => 'QT-2026-NX08',
                    'offset' => 25,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-4MP-WIFI', 'qty' => 4],
                        ['sku' => 'HK-4MP-DOME', 'qty' => 6],
                        ['sku' => 'NVR-16CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-4TB', 'qty' => 1],
                        ['sku' => 'POE-8PORT', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 220],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-NX08',
                    'tech' => $techSuresh,
                    'sched_offset' => 22,
                    'done_offset' => 20,
                    'status' => 'completed',
                    'labor_hours' => 14.0,
                    'custom_rate' => 260,
                    'direct_costs' => 900,
                    'cost_notes' => 'Cable trunking in shared zones.',
                    'notes' => 'Installed and configured Wi-Fi AP and mobile app viewing.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0095',
                    'signer_name' => 'Vikramaditya',
                    'signer_desig' => 'Community Manager',
                    'signer_phone' => '+91 98458 99001',
                    'rating' => 4,
                    'feedback' => 'Cameras working well. Awaiting finance team invoice signoff.',
                    'summary' => 'Installed 4x Wi-Fi Cameras, 6x Dome Cameras & 16-Ch NVR.',
                ],
                'equipment' => [
                    ['sku' => 'HK-4MP-WIFI', 'name' => 'Hikvision 4MP Wi-Fi Camera', 'serial' => 'HKV-4MP-WF-91823', 'mac' => 'BC:92:68:5A:77:01', 'loc' => 'Board Room & Lounge', 'mfg_m' => 12, 'srv_m' => 12],
                ],
                'invoice' => [
                    'no' => 'INV-2026-0049',
                    'offset' => 20,
                    'due_offset' => 6, // Due date was 6 days ago -> Overdue status for receivables aging report!
                    'status' => 'overdue',
                    'pay_offset' => null, // Unpaid / Overdue
                ],
            ],

            // Project 9: Auto Dealership & Workshop (Ravi Teja - Speedway Hyundai)
            [
                'lead' => [
                    'name' => 'Ravi Teja',
                    'company' => 'Speedway Hyundai Dealership',
                    'email' => 'service.head@speedwayhyundai.in',
                    'phone' => '+91 98457 12345',
                    'address' => 'Plot 4, Hosur Main Road, Kudlu Gate, Bangalore',
                    'status' => 'won',
                    'source' => 'Website Inquiry',
                    'created_offset' => 17,
                    'won_offset' => 15,
                    'notes' => '14-Camera installation for new car showroom, customer lounge and service bays.',
                ],
                'survey' => [
                    'tech' => $techRajesh,
                    'offset' => 16,
                    'cams' => 14,
                    'cable' => 380,
                    'dvr_loc' => 'Customer Relations Manager Cabin',
                    'power' => 'Dedicated UPS',
                    'notes' => 'Service bay cameras positioned for customer live inspection.',
                ],
                'quote' => [
                    'no' => 'QT-2026-SW09',
                    'offset' => 15,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-4MP-BULLET', 'qty' => 8],
                        ['sku' => 'HK-4MP-DOME', 'qty' => 6],
                        ['sku' => 'NVR-16CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-4TB', 'qty' => 1],
                        ['sku' => 'POE-16PORT', 'qty' => 1],
                        ['sku' => 'RACK-WALL-6U', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 380],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-SW09',
                    'tech' => $techRajesh,
                    'sched_offset' => 12,
                    'done_offset' => 10,
                    'status' => 'completed',
                    'labor_hours' => 20.0,
                    'custom_rate' => 280,
                    'direct_costs' => 1500,
                    'cost_notes' => 'Workshop overhead metal clamps.',
                    'notes' => 'All 14 cameras operational. Live viewing monitors connected in customer lounge.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0096',
                    'signer_name' => 'Ravi Teja',
                    'signer_desig' => 'Service GM',
                    'signer_phone' => '+91 98457 12345',
                    'rating' => 5,
                    'feedback' => 'Great work. Car service bays are clearly visible on lounge monitors.',
                    'summary' => 'Installed 14x 4MP IP Cameras with 16-Port PoE and live display output.',
                ],
                'equipment' => [
                    ['sku' => 'HK-4MP-BULLET', 'name' => 'Hikvision 4MP Bullet Camera', 'serial' => 'HKV-4MP-SW-99120', 'mac' => 'BC:92:68:5A:88:01', 'loc' => 'Workshop Bay 1 to 6', 'mfg_m' => 24, 'srv_m' => 12],
                ],
                'amc' => [
                    'no' => 'AMC-2026-SW09',
                    'freq' => 'quarterly',
                    'value' => 20000,
                    'start_offset' => 10,
                ],
                'invoice' => [
                    'no' => 'INV-2026-0050',
                    'offset' => 10,
                    'status' => 'paid',
                    'pay_offset' => 8,
                    'pay_method' => 'cheque',
                    'pay_ref' => 'CHQ/AXIS/SPEEDWAY/10291',
                ],
            ],

            // Project 10: Pharmacy Chain (Sangeetha R. - Apollo MedPlus Franchise)
            [
                'lead' => [
                    'name' => 'Sangeetha R.',
                    'company' => 'MedPlus Health Pharmacy',
                    'email' => 'sangeetha@medplusfranchise.in',
                    'phone' => '+91 98452 33441',
                    'address' => '33 11th Main, Jayanagar 4th Block, Bangalore',
                    'status' => 'won',
                    'source' => 'Direct Call',
                    'created_offset' => 13,
                    'won_offset' => 11,
                    'notes' => '6-Camera system for medicine stock room, billing counter and 24x7 shutter.',
                ],
                'survey' => [
                    'tech' => $techBob,
                    'offset' => 12,
                    'cams' => 6,
                    'cable' => 120,
                    'dvr_loc' => 'Pharmacy Manager Office',
                    'power' => 'Inverter UPS',
                    'notes' => 'ColorVu night camera needed for 24-hour night counter.',
                ],
                'quote' => [
                    'no' => 'QT-2026-MP10',
                    'offset' => 11,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-5MP-COLORVU', 'qty' => 2],
                        ['sku' => 'HK-4MP-DOME', 'qty' => 4],
                        ['sku' => 'NVR-8CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-2TB', 'qty' => 1],
                        ['sku' => 'POE-8PORT', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 120],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-MP10',
                    'tech' => $techBob,
                    'sched_offset' => 8,
                    'done_offset' => 7,
                    'status' => 'completed',
                    'labor_hours' => 8.5,
                    'custom_rate' => 250,
                    'direct_costs' => 500,
                    'cost_notes' => 'Conduit casing.',
                    'notes' => 'Medicine inventory shelves and billing counters fully covered.',
                ],
                'jcr' => [
                    'report_no' => 'JCR-2026-0097',
                    'signer_name' => 'Sangeetha R.',
                    'signer_desig' => 'Franchise Partner',
                    'signer_phone' => '+91 98452 33441',
                    'rating' => 5,
                    'feedback' => 'Very happy with the clear night ColorVu view on our 24hr counter.',
                    'summary' => 'Installed 2x ColorVu, 4x Dome Cameras & 8-Ch NVR.',
                ],
                'equipment' => [
                    ['sku' => 'HK-5MP-COLORVU', 'name' => 'Hikvision 5MP ColorVu Bullet', 'serial' => 'HKV-5MP-MED-10291', 'mac' => 'BC:92:68:5A:99:01', 'loc' => '24hr Night Counter & Shutter', 'mfg_m' => 24, 'srv_m' => 12],
                ],
                'amc' => [
                    'no' => 'AMC-2026-MP10',
                    'freq' => 'annually',
                    'value' => 10000,
                    'start_offset' => 7,
                ],
                'invoice' => [
                    'no' => 'INV-2026-0051',
                    'offset' => 7,
                    'status' => 'paid',
                    'pay_offset' => 6,
                    'pay_method' => 'upi',
                    'pay_ref' => 'UPI/PhonePe/MEDPLUS/91823',
                ],
            ],

            // Project 11: Restaurant & Banquet Hall (Harish Chandra - Royal Orchid Banquet) - Scheduled
            [
                'lead' => [
                    'name' => 'Harish Chandra',
                    'company' => 'Royal Orchid Grand Banquets',
                    'email' => 'harish@royalorchidbanquets.com',
                    'phone' => '+91 98455 66778',
                    'address' => 'Plot 88, Old Airport Road, Kodihalli, Bangalore',
                    'status' => 'won',
                    'source' => 'Referral',
                    'created_offset' => 8,
                    'won_offset' => 5,
                    'notes' => '10-Camera surveillance for dining halls, kitchen and valet parking.',
                ],
                'survey' => [
                    'tech' => $techVikram,
                    'offset' => 6,
                    'cams' => 10,
                    'cable' => 280,
                    'dvr_loc' => 'Banquet Manager Office',
                    'power' => 'Commercial UPS',
                    'notes' => 'Client approved proposal. Scheduled for installation next week.',
                ],
                'quote' => [
                    'no' => 'QT-2026-RO11',
                    'offset' => 5,
                    'status' => 'accepted',
                    'items' => [
                        ['sku' => 'HK-4MP-BULLET', 'qty' => 4],
                        ['sku' => 'HK-4MP-DOME', 'qty' => 6],
                        ['sku' => 'NVR-16CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-4TB', 'qty' => 1],
                        ['sku' => 'POE-16PORT', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 280],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
                'job' => [
                    'no' => 'JOB-2026-RO11',
                    'tech' => $techBob,
                    'sched_offset' => -3, // Scheduled 3 days in future
                    'done_offset' => null,
                    'status' => 'scheduled',
                    'notes' => 'Installation scheduled for upcoming Monday morning.',
                ],
            ],

            // Project 12: Boutique Cafe (Karen D'Souza - Cafe Dolce Vista) - Quotation Sent
            [
                'lead' => [
                    'name' => 'Karen D\'Souza',
                    'company' => 'Cafe Dolce Vista',
                    'email' => 'karen@cafedolcevista.in',
                    'phone' => '+91 98459 00112',
                    'address' => '12 Lavelle Road, Bangalore',
                    'status' => 'quoted',
                    'source' => 'Instagram Ad Campaign',
                    'created_offset' => 5,
                    'notes' => '6-Camera aesthetic indoor/outdoor CCTV for boutique coffee shop.',
                ],
                'survey' => [
                    'tech' => $techRajesh,
                    'offset' => 3,
                    'cams' => 6,
                    'cable' => 140,
                    'dvr_loc' => 'Barista Storage Cabinet',
                    'power' => 'Cafe Inverter',
                    'notes' => 'Survey completed. Quotation submitted for approval.',
                ],
                'quote' => [
                    'no' => 'QT-2026-CD12',
                    'offset' => 2,
                    'status' => 'sent',
                    'items' => [
                        ['sku' => 'HK-4MP-DOME', 'qty' => 4],
                        ['sku' => 'HK-4MP-BULLET', 'qty' => 2],
                        ['sku' => 'NVR-8CH', 'qty' => 1],
                        ['sku' => 'HDD-SURV-2TB', 'qty' => 1],
                        ['sku' => 'POE-8PORT', 'qty' => 1],
                        ['sku' => 'CABLE-CAT6', 'qty' => 140],
                        ['sku' => 'SERVICE-CCTV-CONFIG', 'qty' => 1],
                    ],
                ],
            ],

            // Project 13: Manufacturing Factory (Murthy Rao - Titan Precision Engg) - New Lead
            [
                'lead' => [
                    'name' => 'Murthy Rao',
                    'company' => 'Titan Precision Engineering Works',
                    'email' => 'murthy@titanprecisionengg.com',
                    'phone' => '+91 98444 55667',
                    'address' => 'Plot 104, Bommasandra Industrial Estate, Bangalore',
                    'status' => 'new',
                    'source' => 'Website Referral',
                    'created_offset' => 2,
                    'notes' => 'Inquiry for 18 cameras covering CNC machinery shops and dispatch gate.',
                ],
            ],

            // Project 14: Gym & Fitness Arena (Deepa K. - PowerFit Arena) - Lost Lead (for funnel metrics)
            [
                'lead' => [
                    'name' => 'Deepa K.',
                    'company' => 'PowerFit 24/7 Arena',
                    'email' => 'deepa@powerfitarena.in',
                    'phone' => '+91 98441 77889',
                    'address' => 'Koramangala 5th Block, Bangalore',
                    'status' => 'lost',
                    'source' => 'Cold Calling',
                    'created_offset' => 22,
                    'notes' => 'Client postponed security upgrades due to current budget constraints.',
                ],
            ],
        ];

        foreach ($customerProjects as $proj) {
            $lData = $proj['lead'];
            $leadCreated = $now->copy()->subDays($lData['created_offset']);
            $leadUpdated = isset($lData['won_offset']) ? $now->copy()->subDays($lData['won_offset']) : $leadCreated;

            $lead = Lead::updateOrCreate(
                ['email' => $lData['email']],
                [
                    'customer_name' => $lData['name'] . (isset($lData['company']) ? ' (' . $lData['company'] . ')' : ''),
                    'phone' => $lData['phone'],
                    'email' => $lData['email'],
                    'site_address' => $lData['address'],
                    'status' => $lData['status'],
                    'source' => $lData['source'],
                    'notes' => $lData['notes'],
                    'created_at' => $leadCreated,
                    'updated_at' => $leadUpdated,
                ]
            );

            // Customer User Portal Login
            $custUser = User::updateOrCreate(
                ['email' => $lData['email']],
                [
                    'name' => $lData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'lead_id' => $lead->id,
                    'email_verified_at' => $leadCreated,
                ]
            );

            // Site Survey if present
            if (isset($proj['survey'])) {
                $sData = $proj['survey'];
                $surveyDate = $now->copy()->subDays($sData['offset']);
                SiteSurvey::updateOrCreate(
                    ['lead_id' => $lead->id],
                    [
                        'surveyed_by' => $sData['tech']->id,
                        'survey_date' => $surveyDate->toDateString(),
                        'site_address' => $lData['address'],
                        'contact_person' => $lData['name'],
                        'contact_phone' => $lData['phone'],
                        'camera_count_recommended' => $sData['cams'],
                        'dvr_location' => $sData['dvr_loc'],
                        'cable_length_estimate' => $sData['cable'],
                        'power_availability' => $sData['power'],
                        'visit_notes' => $sData['notes'],
                        'status' => 'completed',
                        'created_at' => $surveyDate,
                        'updated_at' => $surveyDate,
                    ]
                );
            }

            // Quotation if present
            $quotation = null;
            if (isset($proj['quote'])) {
                $qData = $proj['quote'];
                $quoteDate = $now->copy()->subDays($qData['offset']);

                $subtotal = 0.0;
                foreach ($qData['items'] as $it) {
                    $prod = $productsBySku[$it['sku']] ?? null;
                    $price = $prod ? (float) $prod->unit_price : 1000.0;
                    $subtotal += ($it['qty'] * $price);
                }
                $taxAmt = round($subtotal * 0.18, 2);
                $total = $subtotal + $taxAmt;

                $quotation = Quotation::updateOrCreate(
                    ['quotation_no' => $qData['no']],
                    [
                        'lead_id' => $lead->id,
                        'quotation_no' => $qData['no'],
                        'subtotal' => $subtotal,
                        'tax_percent' => 18.00,
                        'tax_amount' => $taxAmt,
                        'total' => $total,
                        'status' => $qData['status'],
                        'notes' => "Official Proposal for {$lData['name']}",
                        'created_at' => $quoteDate,
                        'updated_at' => $quoteDate,
                    ]
                );

                // Quotation Items
                foreach ($qData['items'] as $it) {
                    $prod = $productsBySku[$it['sku']] ?? null;
                    if ($prod) {
                        QuotationItem::updateOrCreate(
                            [
                                'quotation_id' => $quotation->id,
                                'product_id' => $prod->id,
                            ],
                            [
                                'item_name' => $prod->name,
                                'description' => $prod->description,
                                'quantity' => $it['qty'],
                                'unit' => $prod->unit,
                                'unit_price' => $prod->unit_price,
                            ]
                        );
                    }
                }
            }

            // Installation Job if present
            $job = null;
            if (isset($proj['job']) && $quotation) {
                $jData = $proj['job'];
                $schedDate = $now->copy()->subDays($jData['sched_offset']);
                $doneDate = isset($jData['done_offset']) ? $now->copy()->subDays($jData['done_offset']) : null;

                $job = InstallationJob::updateOrCreate(
                    ['job_no' => $jData['no']],
                    [
                        'quotation_id' => $quotation->id,
                        'job_no' => $jData['no'],
                        'assigned_technician_id' => $jData['tech']->id,
                        'scheduled_date' => $schedDate->toDateString(),
                        'status' => $jData['status'],
                        'labor_hours_logged' => $jData['labor_hours'] ?? null,
                        'custom_hourly_rate' => $jData['custom_rate'] ?? null,
                        'other_direct_costs' => $jData['direct_costs'] ?? 0.00,
                        'costing_notes' => $jData['cost_notes'] ?? null,
                        'installation_notes' => $jData['notes'] ?? null,
                        'created_at' => $schedDate,
                        'updated_at' => $doneDate ?? $schedDate,
                    ]
                );

                // Expense Claim by Technician for this job
                if ($jData['status'] === 'completed') {
                    $claimNo = 'EXP-' . str_replace('JOB-', '', $jData['no']);
                    ExpenseClaim::firstOrCreate(
                        ['claim_no' => $claimNo],
                        [
                            'user_id' => $jData['tech']->id,
                            'installation_job_id' => $job->id,
                            'expense_category' => 'fuel_travel',
                            'expense_date' => $schedDate->toDateString(),
                            'amount' => 450.00,
                            'travel_distance_km' => 45.0,
                            'travel_from' => 'Head Office HQ',
                            'travel_to' => $lData['address'],
                            'rate_per_km' => 10.0,
                            'description' => "Travel and fuel for {$job->job_no} on-site installation.",
                            'status' => 'paid',
                            'actioned_by' => $admin->id,
                            'actioned_at' => $schedDate,
                            'payment_method' => 'upi',
                            'payment_reference' => 'UPI/EXP/' . rand(10000, 99999),
                            'paid_at' => $schedDate->copy()->addDay(),
                        ]
                    );
                }
            }

            // Job Completion Report (JCR)
            if (isset($proj['jcr']) && $job) {
                $jcrData = $proj['jcr'];
                $completionDate = $job->updated_at;

                JobCompletionReport::updateOrCreate(
                    ['report_no' => $jcrData['report_no']],
                    [
                        'report_no' => $jcrData['report_no'],
                        'lead_id' => $lead->id,
                        'installation_job_id' => $job->id,
                        'technician_id' => $job->assigned_technician_id,
                        'completion_date' => $completionDate,
                        'signer_name' => $jcrData['signer_name'],
                        'signer_designation' => $jcrData['signer_desig'],
                        'signer_phone' => $jcrData['signer_phone'],
                        'customer_rating' => $jcrData['rating'],
                        'customer_feedback' => $jcrData['feedback'],
                        'customer_signature' => $createSignatureSvg($jcrData['signer_name']),
                        'technician_signature' => $createSignatureSvg($job->assignedTechnician->name),
                        'work_summary' => $jcrData['summary'],
                        'status' => 'signed',
                        'created_at' => $completionDate,
                        'updated_at' => $completionDate,
                    ]
                );
            }

            // Installed Equipment
            if (isset($proj['equipment']) && $job) {
                foreach ($proj['equipment'] as $eq) {
                    $prod = $productsBySku[$eq['sku']] ?? null;
                    InstalledEquipment::firstOrCreate(
                        [
                            'lead_id' => $lead->id,
                            'serial_number' => $eq['serial'],
                        ],
                        [
                            'installation_job_id' => $job->id,
                            'product_id' => $prod?->id,
                            'equipment_name' => $eq['name'],
                            'serial_number' => $eq['serial'],
                            'mac_address' => $eq['mac'] ?? null,
                            'location_tag' => $eq['loc'] ?? 'Main Entrance',
                            'installation_date' => $job->updated_at->toDateString(),
                            'manufacturer_warranty_expiry' => $job->updated_at->copy()->addMonths($eq['mfg_m'])->toDateString(),
                            'service_warranty_expiry' => $job->updated_at->copy()->addMonths($eq['srv_m'])->toDateString(),
                            'status' => 'active',
                            'notes' => 'Installed & verified operational.',
                        ]
                    );
                }
            }

            // AMC Contract
            if (isset($proj['amc'])) {
                $amcData = $proj['amc'];
                $amcStart = $now->copy()->subDays($amcData['start_offset']);
                $amc = AmcContract::updateOrCreate(
                    ['lead_id' => $lead->id],
                    [
                        'contract_no' => $amcData['no'],
                        'start_date' => $amcStart->toDateString(),
                        'end_date' => $amcStart->copy()->addMonths(12)->toDateString(),
                        'frequency' => $amcData['freq'],
                        'value' => $amcData['value'],
                        'status' => 'active',
                        'notes' => 'Covers preventive maintenance, cable health check, lens cleaning and priority support.',
                    ]
                );

                // AMC Visit
                AmcVisit::firstOrCreate(
                    ['amc_contract_id' => $amc->id, 'scheduled_date' => $amcStart->copy()->addMonths(3)->toDateString()],
                    [
                        'assigned_technician_id' => $job?->assigned_technician_id ?? $techBob->id,
                        'status' => 'pending',
                        'completion_notes' => 'Q1 Preventive Maintenance scheduled visit.',
                    ]
                );
            }

            // Invoices & Payments
            if (isset($proj['invoice']) && $quotation) {
                $invData = $proj['invoice'];
                $invDate = $now->copy()->subDays($invData['offset']);
                $dueDate = isset($invData['due_offset']) ? $now->copy()->subDays($invData['due_offset']) : $invDate->copy()->addDays(15);

                $total = (float) $quotation->total;
                $subtotal = (float) $quotation->subtotal;
                $taxAmount = (float) $quotation->tax_amount;

                $amountPaid = 0.0;
                if ($invData['status'] === 'paid') {
                    $amountPaid = $total;
                } elseif ($invData['status'] === 'partially_paid') {
                    $pct = $invData['advance_pct'] ?? 0.50;
                    $amountPaid = round($total * $pct, 2);
                }

                $invoice = Invoice::updateOrCreate(
                    ['invoice_no' => $invData['no']],
                    [
                        'installation_job_id' => $job?->id,
                        'quotation_id' => $quotation->id,
                        'invoice_no' => $invData['no'],
                        'invoice_date' => $invDate->toDateString(),
                        'due_date' => $dueDate->toDateString(),
                        'subtotal' => $subtotal,
                        'discount' => 0.00,
                        'tax_percent' => 18.00,
                        'tax_amount' => $taxAmount,
                        'place_of_supply' => 'Karnataka (29)',
                        'place_of_supply_code' => '29',
                        'is_b2b' => isset($lData['company']),
                        'cgst_amount' => round($taxAmount / 2, 2),
                        'sgst_amount' => round($taxAmount / 2, 2),
                        'igst_amount' => 0.00,
                        'total' => $total,
                        'amount_paid' => $amountPaid,
                        'status' => $invData['status'],
                        'created_at' => $invDate,
                        'updated_at' => $invDate,
                    ]
                );

                // Payments Logged
                if ($amountPaid > 0 && isset($invData['pay_offset'])) {
                    $payDate = $now->copy()->subDays($invData['pay_offset']);
                    Payment::firstOrCreate(
                        ['invoice_id' => $invoice->id, 'reference_no' => $invData['pay_ref'] ?? ('RCPT-' . $invData['no'])],
                        [
                            'amount' => $amountPaid,
                            'paid_on' => $payDate->toDateString(),
                            'method' => $invData['pay_method'] ?? 'bank_transfer',
                            'reference_no' => $invData['pay_ref'] ?? ('RCPT-' . $invData['no']),
                            'notes' => "Received settlement for Invoice {$invoice->invoice_no}",
                            'recorded_by' => $admin->id,
                            'created_at' => $payDate,
                            'updated_at' => $payDate,
                        ]
                    );
                }
            }
        }

        // =========================================================================
        // 10. SERVICE TICKETS (TECHNICIAN PERFORMANCE, FTFR & MTTR METRICS)
        // =========================================================================
        $serviceTicketsList = [
            [
                'ticket_no' => 'TKT-2026-101',
                'lead_email' => 'srinithish.p@example.com',
                'tech' => $techBob,
                'creator' => $staffPriya,
                'title' => 'Mobile App Live View Reconfiguration',
                'issue' => 'network_issue',
                'priority' => 'medium',
                'status' => 'resolved',
                'created_offset' => 18,
                'resolved_hours_after' => 2.5,
                'troubleshoot' => 'Checked port forwarding on Wi-Fi router and re-paired Hik-Connect app with QR code.',
                'parts' => 'None (Configuration only)',
                'notes' => 'Successfully restored live streaming and cloud playback on client devices.',
                'first_time_fix' => true,
            ],
            [
                'ticket_no' => 'TKT-2026-102',
                'lead_email' => 'anita.desai@techpark-corp.com',
                'tech' => $techRajesh,
                'creator' => $staffAlex,
                'title' => 'Camera 4 Corridor Feed Intermittent Drop',
                'issue' => 'cable_damaged',
                'priority' => 'high',
                'status' => 'resolved',
                'created_offset' => 15,
                'resolved_hours_after' => 3.0,
                'troubleshoot' => 'Discovered crimping tension near false ceiling duct.',
                'parts' => 'Re-crimped RJ45 gold connector & replaced 10m Cat6 patch section.',
                'notes' => 'PoE power link stable. Ping latency sub 1ms.',
                'first_time_fix' => true,
            ],
            [
                'ticket_no' => 'TKT-2026-103',
                'lead_email' => 'ramesh@greenvalleysuper.com',
                'tech' => $techSuresh,
                'creator' => $staffKesavan,
                'title' => 'Checkout Counter 3 Dome Lens Blurry / Out of Focus',
                'issue' => 'blurry_feed',
                'priority' => 'low',
                'status' => 'resolved',
                'created_offset' => 14,
                'resolved_hours_after' => 2.0,
                'troubleshoot' => 'Fine-tuned varifocal zoom ring and cleaned dust accumulation with optical microfiber.',
                'parts' => 'Optical cleaning kit',
                'notes' => 'Focus razor sharp. Customer confirmed barcode readability on monitor.',
                'first_time_fix' => true,
            ],
            [
                'ticket_no' => 'TKT-2026-104',
                'lead_email' => 'sundaram@lakshmijewels.in',
                'tech' => $techVikram,
                'creator' => $staffPriya,
                'title' => 'Vault Door PTZ Preset Calibration Check',
                'issue' => 'ptz_control_issue',
                'priority' => 'critical',
                'status' => 'resolved',
                'created_offset' => 11,
                'resolved_hours_after' => 1.5,
                'troubleshoot' => 'Recalibrated PTZ motor zero-stop position and remapped RS485 communication baud rate.',
                'parts' => 'Firmware update patch',
                'notes' => 'Patrol cycle working accurately with 25x zoom on safe lock.',
                'first_time_fix' => true,
            ],
            [
                'ticket_no' => 'TKT-2026-105',
                'lead_email' => 'manoj.k@apexlogistics.in',
                'tech' => $techBob,
                'creator' => $staffAlex,
                'title' => 'Gate 2 SMPS 12V Power Supply Tripping',
                'issue' => 'power_supply_issue',
                'priority' => 'high',
                'status' => 'resolved',
                'created_offset' => 9,
                'resolved_hours_after' => 4.0,
                'troubleshoot' => 'Replaced faulty 12V power adapter damaged due to heavy lightning surge.',
                'parts' => '1x 12V DC 8-Channel Surge SMPS Power Supply',
                'notes' => 'New power supply grounded and operational.',
                'first_time_fix' => true,
            ],
            [
                'ticket_no' => 'TKT-2026-106',
                'lead_email' => 'dr.farhan@metrodiag.com',
                'tech' => $techVikram,
                'creator' => $staffKesavan,
                'title' => 'Sample Collection Room HDD Recording Storage Alert',
                'issue' => 'dvr_nvr_beep',
                'priority' => 'medium',
                'status' => 'resolved',
                'created_offset' => 7,
                'resolved_hours_after' => 2.0,
                'troubleshoot' => 'Adjusted overwrite retention cycle and cleaned S.M.A.R.T. warning log.',
                'parts' => 'None',
                'notes' => 'Retention configured to auto-cycle 30 days footage seamlessly.',
                'first_time_fix' => true,
            ],
            [
                'ticket_no' => 'TKT-2026-107',
                'lead_email' => 'service.head@speedwayhyundai.in',
                'tech' => $techRajesh,
                'creator' => $staffPriya,
                'title' => 'Lounge Live Monitor HDMI Video Loss',
                'issue' => 'camera_offline',
                'priority' => 'medium',
                'status' => 'resolved',
                'created_offset' => 5,
                'resolved_hours_after' => 2.5,
                'troubleshoot' => 'Replaced defective 4K HDMI cable connected to NVR Sub-Out.',
                'parts' => '1x 3m High Speed 4K HDMI Cable',
                'notes' => 'Lounge screen broadcasting all 14 service bays in quad layout.',
                'first_time_fix' => true,
            ],
            [
                'ticket_no' => 'TKT-2026-108',
                'lead_email' => 'sangeetha@medplusfranchise.in',
                'tech' => $techSuresh,
                'creator' => $staffAlex,
                'title' => 'Night Counter Camera Motion Detection Notifications',
                'issue' => 'other',
                'priority' => 'low',
                'status' => 'in_progress', // Active in progress
                'created_offset' => 1,
                'resolved_hours_after' => null,
                'troubleshoot' => 'Tuning AcuSense human body filter sensitivity.',
                'parts' => null,
                'notes' => 'Testing push notifications during night hours.',
                'first_time_fix' => false,
            ],
        ];

        foreach ($serviceTicketsList as $stData) {
            $tLead = Lead::where('email', $stData['lead_email'])->first();
            if (!$tLead) continue;

            $createdDate = $now->copy()->subDays($stData['created_offset']);
            $resolvedDate = null;
            if ($stData['status'] === 'resolved' && isset($stData['resolved_hours_after'])) {
                $resolvedDate = $createdDate->copy()->addMinutes((int) ($stData['resolved_hours_after'] * 60));
            }

            ServiceTicket::updateOrCreate(
                ['ticket_no' => $stData['ticket_no']],
                [
                    'lead_id' => $tLead->id,
                    'amc_contract_id' => $tLead->amcContracts()->first()?->id,
                    'assigned_technician_id' => $stData['tech']->id,
                    'created_by_id' => $stData['creator']->id,
                    'title' => $stData['title'],
                    'issue_type' => $stData['issue'],
                    'priority' => $stData['priority'],
                    'status' => $stData['status'],
                    'description' => $stData['troubleshoot'],
                    'scheduled_date' => $createdDate->toDateString(),
                    'troubleshooting_notes' => $stData['troubleshoot'],
                    'parts_replaced' => $stData['parts'],
                    'resolution_notes' => $stData['notes'],
                    'billing_type' => 'warranty_amc',
                    'cost' => 0.00,
                    'resolved_at' => $resolvedDate,
                    'closed_at' => $resolvedDate,
                    'created_at' => $createdDate,
                    'updated_at' => $resolvedDate ?? $createdDate,
                ]
            );
        }

        // =========================================================================
        // 11. RMA CLAIMS & STATUS LOGS
        // =========================================================================
        $rmaProduct = $productsBySku['HK-4MP-BULLET'] ?? null;
        if ($rmaProduct) {
            $rma = RmaClaim::firstOrCreate(
                ['rma_no' => 'RMA-2026-0012'],
                [
                    'lead_id' => Lead::first()?->id,
                    'supplier_id' => $supHikvision->id,
                    'product_id' => $rmaProduct->id,
                    'faulty_serial_number' => 'HKV-4MP-RMA-77182',
                    'issue_description' => 'IR LEDs failed to illuminate in dark conditions. Sensor working.',
                    'fault_category' => 'ir_led_failure',
                    'warranty_status_at_claim' => 'under_warranty',
                    'status' => 'replaced',
                    'resolution_type' => 'replacement',
                    'replacement_serial_number' => 'HKV-4MP-REP-99218',
                    'vendor_rma_ref' => 'HIK-RMA-BLR-881920',
                    'vendor_repair_notes' => 'Replaced with brand new factory unit under 2-year manufacturer warranty.',
                    'created_by' => $admin->id,
                    'created_at' => $now->copy()->subDays(15),
                    'updated_at' => $now->copy()->subDays(6),
                ]
            );

            RmaStatusLog::firstOrCreate(
                ['rma_claim_id' => $rma->id, 'to_status' => 'replaced'],
                [
                    'from_status' => 'in_vendor_repair',
                    'notes' => 'Received replacement unit from Hikvision service depot.',
                    'changed_by' => $admin->id,
                ]
            );
        }

        // =========================================================================
        // 12. GST FILINGS FOR FINANCIAL COMPLIANCE
        // =========================================================================
        $lastMonthKey = $lastMonthStart->format('Y-m');
        $currMonthKey = $startOfMonth->format('Y-m');

        GstFiling::firstOrCreate(
            ['period' => $lastMonthKey],
            [
                'gstr1_status' => 'filed',
                'gstr3b_status' => 'filed',
                'total_turnover' => 540000.00,
                'taxable_turnover' => 457627.00,
                'output_tax' => 82373.00,
                'input_tax_credit' => 46500.00,
                'net_tax_payable' => 35873.00,
                'tax_paid' => 35873.00,
                'challan_no' => 'CPIN2608192801',
                'cin_number' => 'HDFC26081928001',
                'filing_date' => $now->copy()->subDays(15)->toDateString(),
                'payment_mode' => 'online_portal',
                'notes' => 'Filed and fully reconciled GSTR-1 and GSTR-3B for previous tax period.',
                'filed_by' => $admin->id,
            ]
        );

        GstFiling::firstOrCreate(
            ['period' => $currMonthKey],
            [
                'gstr1_status' => 'reconciled',
                'gstr3b_status' => 'pending',
                'total_turnover' => 620000.00,
                'taxable_turnover' => 525423.00,
                'output_tax' => 94577.00,
                'input_tax_credit' => 51200.00,
                'net_tax_payable' => 43377.00,
                'tax_paid' => 0.00,
                'notes' => 'Current tax month running sales invoices reconciled, awaiting filing deadline.',
                'filed_by' => $admin->id,
            ]
        );
    }
}
