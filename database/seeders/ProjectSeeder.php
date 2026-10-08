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

        // Project 1: Hardware CCTV (In Progress)
        $p1 = Project::updateOrCreate(
            ['project_code' => 'PRJ-2026-0001'],
            [
                'title' => '32-Camera 4K IP Setup & Fiber Backbone Installation',
                'project_type' => 'hardware_cctv',
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
                'hardware_specs' => [
                    'camera_count' => 32,
                    'terminal_count' => 0,
                    'device_brand' => 'Hikvision DarkFighter',
                    'terminal_ip' => '192.168.10.0/24',
                    'attendance_sync_mode' => 'n_a',
                    'cloud_sync_enabled' => true,
                ],
            ]
        );

        // Project 2: Hardware Terminal Camera & Attendance Management (Completed)
        $p2 = Project::updateOrCreate(
            ['project_code' => 'PRJ-2026-0002'],
            [
                'title' => 'AI Face-Recognition Terminal Camera Attendance Management System',
                'project_type' => 'hardware_attendance',
                'company_name' => 'Metro Mart Retail Corp',
                'site_address' => 'Flagship Hypermarket, MG Road Mall, Bengaluru',
                'contact_person' => 'Pooja Sharma',
                'contact_phone' => '+91 97411 67890',
                'contact_email' => 'security@metromartretail.com',
                'description' => 'Deployment of 8 high-speed AI dual-lens facial recognition biometric terminals linked with employee shifts, synchronized with staff attendance records, plus 16 POS checkout security cameras.',
                'status' => 'completed',
                'priority' => 'urgent',
                'progress_percentage' => 100,
                'budget' => 320000.00,
                'actual_cost' => 295000.00,
                'start_date' => now()->subDays(30)->toDateString(),
                'deadline' => now()->subDays(5)->toDateString(),
                'completed_at' => now()->subDays(3)->toDateString(),
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
                'notes' => 'Successfully commissioned. All 8 terminals syncing live attendance punches to CRM every 60 seconds.',
                'hardware_specs' => [
                    'terminal_count' => 8,
                    'camera_count' => 16,
                    'device_brand' => 'ZKTeco ProFace X / Hikvision MinMoe',
                    'terminal_ip' => '10.0.4.50 - 10.0.4.57',
                    'attendance_sync_mode' => 'face_recognition',
                    'cloud_sync_enabled' => true,
                ],
            ]
        );

        // Project 3: Software Webpage & Customer Web Portal (In Progress)
        $p3 = Project::updateOrCreate(
            ['project_code' => 'PRJ-2026-0003'],
            [
                'title' => 'Client Self-Service Webpage & Live Attendance Analytics Portal',
                'project_type' => 'software_web',
                'company_name' => 'Apex Logistics & Warehousing',
                'site_address' => 'Plot 88, Hoskote Industrial Area, Bengaluru East',
                'contact_person' => 'Arun Verma',
                'contact_phone' => '+91 99000 54321',
                'contact_email' => 'admin@apexlogistics.in',
                'description' => 'Responsive web portal for supply chain clients: real-time camera streaming viewer, warehouse shift attendance tracking, digital gate pass issuance, and automated billing invoices.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'progress_percentage' => 70,
                'budget' => 240000.00,
                'actual_cost' => 140000.00,
                'start_date' => now()->subDays(15)->toDateString(),
                'deadline' => now()->addDays(12)->toDateString(),
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
                'notes' => 'Phase 1 frontend UI wireframes approved. Integrating live RTSP WebRTC stream transcoders and staff shift roster API.',
                'software_specs' => [
                    'webpage_url' => 'https://portal.apexlogistics.in',
                    'repository_url' => 'https://github.com/apex-logistics/portal-web',
                    'tech_stack' => 'Laravel 11, Tailwind CSS, Alpine.js, WebRTC, MySQL',
                    'deployment_server' => 'Ubuntu 24.04 LTS / Nginx / Cloudflare SSL',
                    'milestones' => [
                        ['name' => 'UI Wireframes & Responsive Layouts', 'status' => 'completed'],
                        ['name' => 'Staff Attendance API & Webhook Ingestion', 'status' => 'completed'],
                        ['name' => 'Camera Stream RTSP Transcoder', 'status' => 'in_progress'],
                        ['name' => 'Client UAT Sign-off', 'status' => 'pending'],
                    ],
                ],
            ]
        );

        // Project 4: Hybrid Turnkey Project (Incompleted / Pending)
        $p4 = Project::updateOrCreate(
            ['project_code' => 'PRJ-2026-0004'],
            [
                'title' => 'Smart Industrial Campus: 24 CCTV Cameras + 4 Terminal Attendance + Monitoring Webpage',
                'project_type' => 'hybrid',
                'company_name' => 'Zenith Precision Manufacturing Ltd',
                'site_address' => 'Peenya Industrial Estate Phase 3, Bengaluru',
                'contact_person' => 'Ramesh Chander',
                'contact_phone' => '+91 98860 77112',
                'contact_email' => 'operations@zenithprecision.com',
                'description' => 'Integrated hardware deployment with 24 PoE bullet cameras, 4 biometric face turnstile terminals at entry gates, connected to an internal company web portal for attendance logs.',
                'status' => 'incompleted',
                'priority' => 'urgent',
                'progress_percentage' => 35,
                'budget' => 580000.00,
                'actual_cost' => 195000.00,
                'start_date' => now()->subDays(20)->toDateString(),
                'deadline' => now()->addDays(8)->toDateString(),
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
                'notes' => 'Gate turnstiles mounted. Network switches delivered. Waiting on ISP static IP allocation to complete web portal integration.',
                'hardware_specs' => [
                    'terminal_count' => 4,
                    'camera_count' => 24,
                    'device_brand' => 'Hikvision MinMoe + Uniview 4K',
                    'terminal_ip' => '172.16.1.10 - 172.16.1.14',
                    'attendance_sync_mode' => 'face_recognition',
                    'cloud_sync_enabled' => true,
                ],
                'software_specs' => [
                    'webpage_url' => 'https://security.zenithprecision.com',
                    'repository_url' => 'https://github.com/zenith-mfg/plant-dashboard',
                    'tech_stack' => 'Laravel, Chart.js, Tailwind, REST API',
                    'deployment_server' => 'Internal Docker Host / WireGuard VPN',
                    'milestones' => [
                        ['name' => 'Network Architecture Design', 'status' => 'completed'],
                        ['name' => 'Gate Terminal Cabling & Turnstile Setup', 'status' => 'in_progress'],
                        ['name' => 'Attendance Management Webpage UI', 'status' => 'in_progress'],
                        ['name' => 'Final Factory Commissioning', 'status' => 'pending'],
                    ],
                ],
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
