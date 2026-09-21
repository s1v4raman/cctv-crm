<?php

namespace Database\Seeders;
use Database\Seeders\ProductSeeder;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        // Ensure Products are seeded
        $this->call([
            ProductSeeder::class,
        ]);

        // 1. Admin: Test User
        $admin = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User (Admin)', 'password' => bcrypt('password'), 'role' => 'admin', 'email_verified_at' => now()]
        );
        $admin->update(['role' => 'admin', 'name' => 'Test User', 'password' => bcrypt('password')]);

        // 2. Employee / Staff: Alex Rivera
        $staff = User::firstOrCreate(
            ['email' => 'alex@example.com'],
            ['name' => 'Alex Rivera (Staff)', 'password' => bcrypt('password'), 'role' => 'staff', 'email_verified_at' => now()]
        );
        $staff->update(['role' => 'staff', 'name' => 'Alex Rivera', 'password' => bcrypt('password')]);

        // 3. Technician: Bob Miller
        $technician = User::firstOrCreate(
            ['email' => 'bob@example.com'],
            ['name' => 'Bob Miller (Technician)', 'password' => bcrypt('password'), 'role' => 'technician', 'email_verified_at' => now()]
        );
        $technician->update(['role' => 'technician', 'name' => 'Bob Miller', 'password' => bcrypt('password')]);

        // 4. Customer: srinithish.p
        // Create Lead for Srinithish P
        $lead = \App\Models\Lead::firstOrCreate(
            ['email' => 'srinithish.p@example.com'],
            [
                'customer_name' => 'Srinithish P',
                'phone' => '+91 98765 43210',
                'email' => 'srinithish.p@example.com',
                'site_address' => '74/2 Cyber Tech Residency, Bangalore',
                'status' => 'won',
                'source' => 'Website Referral',
                'notes' => '8-Camera 4K CCTV surveillance system with 1-Year AMC contract and remote cloud backup.',
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'srinithish.p@example.com'],
            [
                'name' => 'srinithish.p',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'lead_id' => $lead->id,
                'email_verified_at' => now(),
            ]
        );
        $customer->update(['role' => 'customer', 'name' => 'srinithish.p', 'lead_id' => $lead->id, 'password' => bcrypt('password')]);

        // Products for linking
        $camProduct = \App\Models\Product::first();
        $nvrProduct = \App\Models\Product::skip(1)->first() ?? $camProduct;

        // Create Quotation for Srinithish P
        $quotation = \App\Models\Quotation::firstOrCreate(
            ['quotation_no' => 'QT-2026-SRI01'],
            [
                'lead_id' => $lead->id,
                'quotation_no' => 'QT-2026-SRI01',
                'subtotal' => 45000,
                'tax_percent' => 18,
                'tax_amount' => 8100,
                'total' => 53100,
                'status' => 'accepted',
                'notes' => 'Includes 4MP Dome & Bullet Cameras, 8-Channel NVR, 2TB Surveillance Hard Drive & Installation.',
            ]
        );

        // Site Survey for Srinithish P
        $survey = \App\Models\SiteSurvey::firstOrCreate(
            ['lead_id' => $lead->id],
            [
                'lead_id' => $lead->id,
                'surveyed_by' => $technician->id,
                'survey_date' => now()->subDays(6)->toDateString(),
                'site_address' => '74/2 Cyber Tech Residency, Bangalore',
                'contact_person' => 'Srinithish P',
                'contact_phone' => '+91 98765 43210',
                'camera_count_recommended' => 8,
                'dvr_location' => 'Ground Floor Server Rack',
                'cable_length_estimate' => 180,
                'power_availability' => 'Dedicated 1kVA UPS Available',
                'visit_notes' => 'Survey completed by Bob Miller. Client approved camera mounting locations.',
                'status' => 'completed',
            ]
        );

        // Installation Job
        $job = \App\Models\InstallationJob::firstOrCreate(
            ['job_no' => 'JOB-2026-SRI01'],
            [
                'quotation_id' => $quotation->id,
                'job_no' => 'JOB-2026-SRI01',
                'assigned_technician_id' => $technician->id,
                'scheduled_date' => now()->subDays(3)->toDateString(),
                'status' => 'completed',
                'installation_notes' => 'All 8 cameras mounted, cabling trunked cleanly, NVR configured with remote mobile app access.',
            ]
        );

        // Installed Equipment with Serial Tracking & Warranty
        if (\App\Models\InstalledEquipment::where('lead_id', $lead->id)->count() == 0) {
            \App\Models\InstalledEquipment::create([
                'lead_id' => $lead->id,
                'installation_job_id' => $job->id,
                'product_id' => $camProduct?->id,
                'equipment_name' => 'Hikvision 4MP ColorVu IP Dome Camera',
                'serial_number' => 'HKV-4MP-984210',
                'mac_address' => 'BC:92:68:5A:11:01',
                'location_tag' => 'Main Reception Entrance',
                'installation_date' => now()->subDays(3)->toDateString(),
                'manufacturer_warranty_expiry' => now()->addMonths(24)->toDateString(),
                'service_warranty_expiry' => now()->addMonths(12)->toDateString(),
                'status' => 'active',
                'notes' => 'Tested 4MP ColorVu Night Vision with PoE',
            ]);
            \App\Models\InstalledEquipment::create([
                'lead_id' => $lead->id,
                'installation_job_id' => $job->id,
                'product_id' => $nvrProduct?->id,
                'equipment_name' => 'Hikvision 8-Channel 4K NVR with 2TB HDD',
                'serial_number' => 'NVR-8CH-4K-55219',
                'mac_address' => 'BC:92:68:5A:11:99',
                'location_tag' => 'Server Room / IT Rack',
                'installation_date' => now()->subDays(3)->toDateString(),
                'manufacturer_warranty_expiry' => now()->addMonths(36)->toDateString(),
                'service_warranty_expiry' => now()->addMonths(12)->toDateString(),
                'status' => 'active',
                'notes' => '8-Channel 4K NVR with 2TB WD Purple HDD',
            ]);
        }

        // AMC Contract
        $amc = \App\Models\AmcContract::firstOrCreate(
            ['lead_id' => $lead->id],
            [
                'lead_id' => $lead->id,
                'contract_no' => 'AMC-2026-SRI',
                'start_date' => now()->subDays(3)->toDateString(),
                'end_date' => now()->addMonths(12)->toDateString(),
                'frequency' => 'quarterly',
                'value' => 12000,
                'status' => 'active',
                'notes' => 'Covers quarterly lens cleaning, cable health audit, NVR firmware updates & priority breakdown support.',
            ]
        );

        // AMC Scheduled Visits
        if (\App\Models\AmcVisit::where('amc_contract_id', $amc->id)->count() == 0) {
            \App\Models\AmcVisit::create([
                'amc_contract_id' => $amc->id,
                'scheduled_date' => now()->addMonths(3)->toDateString(),
                'assigned_technician_id' => $technician->id,
                'status' => 'pending',
                'completion_notes' => 'Q1 Scheduled Preventive Maintenance & Lens Cleaning',
            ]);
        }

        // Service Ticket for Srinithish P
        $ticket = \App\Models\ServiceTicket::firstOrCreate(
            ['ticket_no' => 'TKT-2026-1082'],
            [
                'ticket_no' => 'TKT-2026-1082',
                'lead_id' => $lead->id,
                'amc_contract_id' => $amc->id,
                'assigned_technician_id' => $technician->id,
                'created_by_id' => $customer->id,
                'title' => 'Mobile App Live View Reconfiguration',
                'description' => 'Customer requested port forwarding check and setup of Hik-Connect on new iPad.',
                'issue_type' => 'network_issue',
                'priority' => 'medium',
                'status' => 'in_progress',
                'scheduled_date' => now()->addDays(1)->toDateString(),
                'billing_type' => 'warranty_amc',
            ]
        );

        // Invoice & Payment
        $invoice = \App\Models\Invoice::firstOrCreate(
            ['invoice_no' => 'INV-2026-0042'],
            [
                'installation_job_id' => $job->id,
                'quotation_id' => $quotation->id,
                'invoice_no' => 'INV-2026-0042',
                'invoice_date' => now()->subDays(3)->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'subtotal' => 45000,
                'tax_percent' => 18,
                'tax_amount' => 8100,
                'total' => 53100,
                'amount_paid' => 53100,
                'status' => 'paid',
            ]
        );

        \App\Models\Payment::firstOrCreate(
            ['invoice_id' => $invoice->id, 'reference_no' => 'RCPT-2026-9041'],
            [
                'invoice_id' => $invoice->id,
                'amount' => 53100,
                'paid_on' => now()->subDays(2)->toDateString(),
                'method' => 'upi',
                'reference_no' => 'RCPT-2026-9041',
                'notes' => 'Paid in full via Razorpay Gateway (Ref: pay_Q9xL492aB1).',
                'recorded_by' => $admin->id,
            ]
        );

        // Job Completion Report (JCR)
        \App\Models\JobCompletionReport::firstOrCreate(
            ['report_no' => 'JCR-2026-0089'],
            [
                'report_no' => 'JCR-2026-0089',
                'lead_id' => $lead->id,
                'installation_job_id' => $job->id,
                'technician_id' => $technician->id,
                'completion_date' => now()->subDays(2),
                'signer_name' => 'Srinithish P',
                'signer_designation' => 'Property Owner',
                'signer_phone' => '+91 98765 43210',
                'customer_rating' => 5,
                'customer_feedback' => 'Excellent neat installation and clear camera quality!',
                'customer_signature' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="300" height="100"><text x="10" y="50" font-family="Brush Script MT, cursive" font-size="30" fill="%231e3a8a">Srinithish P</text></svg>',
                'technician_signature' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="300" height="100"><text x="10" y="50" font-family="Brush Script MT, cursive" font-size="30" fill="%231e3a8a">Bob Miller</text></svg>',
                'work_summary' => 'Installed 8x 4MP IP Cameras with Night ColorVu, 8-Ch NVR, Cat6 Cabling & Conduit. Live streaming active on client devices.',
                'status' => 'signed',
            ]
        );
    }
}

