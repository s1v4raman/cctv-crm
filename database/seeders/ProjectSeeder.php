<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) {
            return;
        }

        // Project 1: In Progress
        $p1 = Project::updateOrCreate(
            ['project_code' => 'PRJ-2026-0001'],
            [
                'title' => '32-Camera 4K IP Setup & Fiber Backbone Installation',
                'company_name' => 'TechPark Infosolutions Pvt Ltd',
                'site_address' => 'Tower B, Outer Ring Road Tech Zone, Bengaluru',
                'contact_person' => 'Suresh Reddy',
                'contact_phone' => '+91 98450 12345',
                'contact_email' => 'facilities@techparkinfo.com',
                'description' => 'Installation of 32 Hikvision 4K DarkFighter IP dome cameras, Cat6 FTP shielded cabling, 2x 16-Port PoE+ Gigabit Switches, and centralized 32-Ch NVR with 30-day RAID storage.',
                'status' => 'in_progress',
                'priority' => 'high',
                'progress_percentage' => 65,
                'budget' => 450000.00,
                'actual_cost' => 280000.00,
                'start_date' => now()->subDays(10)->toDateString(),
                'deadline' => now()->addDays(14)->toDateString(),
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
                'notes' => 'Ground floor cabling finished. 1st and 2nd floor camera mounting pending ceiling tile work.',
            ]
        );

        // Project 2: Done / Completed
        $p2 = Project::updateOrCreate(
            ['project_code' => 'PRJ-2026-0002'],
            [
                'title' => '16-Channel AI Face Recognition & POS Surveillance',
                'company_name' => 'Metro Mart Retail Corp',
                'site_address' => 'Flagship Hypermarket, MG Road Mall, Bengaluru',
                'contact_person' => 'Pooja Sharma',
                'contact_phone' => '+91 97411 67890',
                'contact_email' => 'security@metromartretail.com',
                'description' => 'Complete CCTV deployment with AI heatmapping and POS cash register overlay integration across 12 billing counters.',
                'status' => 'completed',
                'priority' => 'medium',
                'progress_percentage' => 100,
                'budget' => 285000.00,
                'actual_cost' => 270000.00,
                'start_date' => now()->subDays(30)->toDateString(),
                'deadline' => now()->subDays(5)->toDateString(),
                'completed_at' => now()->subDays(3)->toDateString(),
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
                'notes' => 'Successfully commissioned. Customer handover certificate signed and AMC initiated.',
            ]
        );

        // Project 3: Incompleted / Pending
        $p3 = Project::updateOrCreate(
            ['project_code' => 'PRJ-2026-0003'],
            [
                'title' => 'High-Mast Perimeter PTZ & Solar Powered CCTV Towers',
                'company_name' => 'Apex Logistics & Warehousing',
                'site_address' => 'Plot 88, Hoskote Industrial Area, Bengaluru East',
                'contact_person' => 'Arun Verma',
                'contact_phone' => '+91 99000 54321',
                'contact_email' => 'admin@apexlogistics.in',
                'description' => 'Perimeter fence surveillance using long-range 45x optical zoom laser PTZ cameras and solar battery backup towers.',
                'status' => 'incompleted',
                'priority' => 'urgent',
                'progress_percentage' => 25,
                'budget' => 620000.00,
                'actual_cost' => 150000.00,
                'start_date' => now()->subDays(15)->toDateString(),
                'deadline' => now()->addDays(5)->toDateString(),
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
                'notes' => 'Civil foundation completed for 2 poles. Delay in solar battery supply from manufacturer. Incomplete pending delivery.',
            ]
        );

        // Sample PDF for Project 1
        $pdfPath = 'project_documents/TechPark_CCTV_Blueprint_v1.pdf';
        $pdfRaw = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n4 0 obj\n<< /Length 73 >>\nstream\nBT\n/F1 18 Tf\n50 720 Td\n(TechPark Infosolutions - CCTV Network Blueprint v1.0) Tj\nET\nendstream\nendobj\n5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\nxref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000244 00000 n \n0000000368 00000 n \ntrailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n447\n%%EOF";

        Storage::disk('public')->put($pdfPath, $pdfRaw);

        ProjectDocument::updateOrCreate(
            [
                'project_id' => $p1->id,
                'file_name' => 'TechPark_CCTV_Blueprint_v1.pdf',
            ],
            [
                'title' => 'Architectural CCTV Floorplan Blueprint',
                'file_path' => $pdfPath,
                'file_size' => strlen($pdfRaw),
                'file_type' => 'pdf',
                'uploaded_by' => $admin->id,
                'notes' => 'Floor plan approved by site structural engineer.'
            ]
        );
    }
}
