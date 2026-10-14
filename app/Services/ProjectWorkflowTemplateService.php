<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectTask;
use App\Models\ProjectTaskChecklist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectWorkflowTemplateService
{
    /**
     * Get all available industry workflow templates.
     */
    public static function getAllTemplates(): array
    {
        return [
            'cctv_commercial' => [
                'name' => 'Commercial CCTV Surveillance (Turnkey 5-Phase)',
                'category' => 'hardware_cctv',
                'description' => 'Comprehensive enterprise workflow covering civil pathway, Cat6 cabling, camera mounting, static IP allocation, NVR configuration, and client night-vision handover.',
                'estimated_days' => 5,
                'phases' => [
                    [
                        'phase_name' => 'Phase 1: Site Survey & Pathway Planning',
                        'tasks' => [
                            [
                                'title' => 'Site Layout & Camera Blind Spot Assessment',
                                'description' => 'Conduct physical walkthrough to verify mounting angles, focal distances, lighting conditions, and power supply locations.',
                                'priority' => 'high',
                                'estimated_hours' => 3.0,
                                'checklists' => [
                                    'Identify key entry/exit monitoring points',
                                    'Check direct sunlight / backlighting glare',
                                    'Confirm NVR rack location and 230V AC socket availability',
                                    'Obtain site layout markup approval from client',
                                ],
                            ],
                            [
                                'title' => 'Civil & Conduit Route Approval',
                                'description' => 'Determine optimal conduit paths (PVC/GI/Casing-capping) and mark core cutting points.',
                                'priority' => 'medium',
                                'estimated_hours' => 2.5,
                                'checklists' => [
                                    'Mark core cut locations on concrete/brick walls',
                                    'Confirm false-ceiling access and trapdoors',
                                    'Verify clearance from high-voltage 415V electrical lines',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 2: Cabling & Infrastructure',
                        'tasks' => [
                            [
                                'title' => 'Cat6/Coaxial Cable Pulling & Ferrule Tagging',
                                'description' => 'Pull structured cables from rack location to each camera point with standard ferrule labeling.',
                                'priority' => 'high',
                                'estimated_hours' => 8.0,
                                'checklists' => [
                                    'Pull 100% pure copper Cat6 / 3+1 cables',
                                    'Leave 1.5m service loop at camera end and 3m at rack end',
                                    'Affix ferrule number tags at both cable ends (CAM-01, CAM-02...)',
                                    'Record total metre consumption in Project Materials',
                                ],
                            ],
                            [
                                'title' => 'Conduit Piping & Weatherproof Junction Boxes',
                                'description' => 'Fasten conduit pipes with heavy-duty saddle clamps and install IP66 outdoor junction boxes.',
                                'priority' => 'medium',
                                'estimated_hours' => 6.0,
                                'checklists' => [
                                    'Saddle clamps fixed every 1 metre',
                                    'IP66 junction boxes mounted at each camera point',
                                    'Apply weatherproof silicone sealant to outdoor entry holes',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 3: Hardware Mounting & Rack Setup',
                        'tasks' => [
                            [
                                'title' => 'Network Rack & Patch Panel Dressing',
                                'description' => 'Wall-mount 4U/6U rack, terminate Cat6 cables on patch panel, and install PDU.',
                                'priority' => 'high',
                                'estimated_hours' => 4.0,
                                'checklists' => [
                                    'Mount 4U/6U/9U network rack securely with anchor fasteners',
                                    'Punch down Cat6 cables to patch panel using T568B color code',
                                    'Dress patch cords neatly with hook-and-loop velcro ties',
                                    'Connect rack earthing copper wire to electrical earth',
                                ],
                            ],
                            [
                                'title' => 'Camera Mounting & Waterproof RJ45 Crimping',
                                'description' => 'Securely mount dome/bullet cameras and fit waterproof connector boots.',
                                'priority' => 'high',
                                'estimated_hours' => 6.0,
                                'checklists' => [
                                    'Crimp high-grade shielded RJ45 connectors',
                                    'Fit waterproof RJ45 connector boots on all outdoor cameras',
                                    'Mount cameras securely on junction box base lids',
                                    'Ensure camera drip loops are positioned to shed rainwater',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 4: IP Allocation & NVR Commissioning',
                        'tasks' => [
                            [
                                'title' => 'PoE Switch Setup & Static IP Registry',
                                'description' => 'Power PoE switch, assign reserved static IP addresses to cameras, and log credentials in IP Registry.',
                                'priority' => 'urgent',
                                'estimated_hours' => 3.5,
                                'checklists' => [
                                    'Verify PoE wattage budget across all channels',
                                    'Configure static IP subnets (e.g. 192.168.1.101 - 192.168.1.132)',
                                    'Change default factory passwords immediately',
                                    'Add all camera IPs and credentials to Project IP Devices registry',
                                ],
                            ],
                            [
                                'title' => 'NVR Channel Mapping & Hard Drive Setup',
                                'description' => 'Format surveillance HDDs, map camera streams, configure recording schedules.',
                                'priority' => 'high',
                                'estimated_hours' => 3.0,
                                'checklists' => [
                                    'Format Western Digital Purple / Seagate SkyHawk HDDs',
                                    'Verify 24/7 continuous recording profile on mainstream',
                                    'Enable H.265+ / Ultra H.265 compression codec',
                                    'Configure NTP server sync for accurate timestamp stamping',
                                ],
                            ],
                            [
                                'title' => 'Motion Detection & Smart AI Analytics Calibration',
                                'description' => 'Set up line crossing, perimeter intrusion detection, and sensitivity zones.',
                                'priority' => 'medium',
                                'estimated_hours' => 2.5,
                                'checklists' => [
                                    'Draw intrusion detection zones for perimeter cameras',
                                    'Adjust human / vehicle classification AI filters',
                                    'Mask private neighbor windows from video view (privacy masking)',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 5: Client Commissioning & Handover',
                        'tasks' => [
                            [
                                'title' => 'Night Vision & IR Glare Inspection',
                                'description' => 'Inspect night video feed after dusk to eliminate IR glare and adjust camera angles.',
                                'priority' => 'high',
                                'estimated_hours' => 2.0,
                                'checklists' => [
                                    'Inspect feeds after dark for wall/soffit IR reflection glare',
                                    'Adjust camera tilt to maximize night illumination field',
                                    'Verify number plate capture on driveway entrance camera',
                                ],
                            ],
                            [
                                'title' => 'Mobile App & Client PC Remote Viewing Configuration',
                                'description' => 'Set up mobile application (Hik-Connect / DMSS / Provision Cam2) on client phones and PCs.',
                                'priority' => 'high',
                                'estimated_hours' => 2.0,
                                'checklists' => [
                                    'Configure router port forwarding or secure cloud P2P',
                                    'Install and test viewing app on client primary smartphones',
                                    'Create secondary restricted user account for security guards',
                                    'Test live feed streaming over 4G/5G mobile data',
                                ],
                            ],
                            [
                                'title' => 'Client Walkthrough & Job Completion Report (JCR) Sign-off',
                                'description' => 'Conduct system walkthrough, handover warranty documentation, and get digital signature on JCR.',
                                'priority' => 'urgent',
                                'estimated_hours' => 1.5,
                                'checklists' => [
                                    'Demonstrate footage search and USB export to client',
                                    'Hand over envelope with sealed master admin credentials',
                                    'Deliver product serial numbers and warranty cards',
                                    'Obtain client physical/digital signature on Job Completion Report',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'biometric_attendance' => [
                'name' => 'Biometric Attendance & Access Control Workflow',
                'category' => 'hardware_attendance',
                'description' => 'Specialized workflow for facial recognition terminals, EM locks, exit switches, and cloud HR attendance integration.',
                'estimated_days' => 3,
                'phases' => [
                    [
                        'phase_name' => 'Phase 1: Power & Network Drops',
                        'tasks' => [
                            [
                                'title' => 'Dedicated LAN Drop & 12V DC Backup Power Planning',
                                'description' => 'Install Cat6 network drop from switch to entrance and wire 12V 5A battery-backed power supply.',
                                'priority' => 'high',
                                'estimated_hours' => 3.0,
                                'checklists' => [
                                    'Pull shielded Cat6 from server room to door entrance',
                                    'Install dedicated 12V 5A Power Supply Unit with 7Ah SMF battery backup',
                                    'Verify steady 12.4V - 13.8V DC power output',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 2: Hardware & EM Lock Installation',
                        'tasks' => [
                            [
                                'title' => 'Terminal Wall Mounting & Ergonomic Alignment',
                                'description' => 'Mount facial recognition / fingerprint terminal at optimal user height.',
                                'priority' => 'medium',
                                'estimated_hours' => 2.0,
                                'checklists' => [
                                    'Mount terminal at 1.45m height for optimal camera face capture',
                                    'Verify camera is not blinded by direct outdoor sunlight',
                                    'Wire tamper switch alarm circuit',
                                ],
                            ],
                            [
                                'title' => 'EM Magnetic Lock, ZL Bracket & Push-To-Exit Switch',
                                'description' => 'Mount 600lb/1200lb EM lock on door frame and wire no-touch exit sensor.',
                                'priority' => 'high',
                                'estimated_hours' => 4.0,
                                'checklists' => [
                                    'Mount heavy-duty ZL/U bracket on glass or wooden door',
                                    'Align armature plate and electromagnetic coil without gap',
                                    'Install infrared touchless exit switch inside room',
                                    'Wire emergency break-glass unit in series for life safety',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 3: Software & Employee Biometric Enrollment',
                        'tasks' => [
                            [
                                'title' => 'Network Configuration & Cloud HR Sync',
                                'description' => 'Assign static IP, configure gateway, and connect device to cloud payroll software.',
                                'priority' => 'urgent',
                                'estimated_hours' => 2.5,
                                'checklists' => [
                                    'Configure static IP, Subnet Mask, and Gateway',
                                    'Set Cloud Server IP / ADMS domain address',
                                    'Sync device date & time with NTP server',
                                    'Test real-time punch event push to HR dashboard',
                                ],
                            ],
                            [
                                'title' => 'Employee Face, Fingerprint & RFID Enrollment',
                                'description' => 'Enroll company staff biometric records and configure access shift groups.',
                                'priority' => 'high',
                                'estimated_hours' => 4.0,
                                'checklists' => [
                                    'Enroll System Admin master face/fingerprint',
                                    'Batch enroll employee profiles with ID numbers',
                                    'Set access schedule (e.g. 9 AM to 7 PM Monday-Saturday)',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 4: Fire Alarm Interlock & Handover',
                        'tasks' => [
                            [
                                'title' => 'Fail-Safe Fire Relay Testing & HR Training',
                                'description' => 'Verify door automatically unlocks when power fails or fire alarm triggers, and train HR admin.',
                                'priority' => 'high',
                                'estimated_hours' => 2.0,
                                'checklists' => [
                                    'Test power cut: verify door unlocks immediately (fail-safe mode)',
                                    'Test emergency break-glass functionality',
                                    'Train HR administrator on downloading daily attendance reports',
                                    'Handover USB backup key and admin credentials',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'structured_networking' => [
                'name' => 'Structured Cabling & Enterprise Wi-Fi Network',
                'category' => 'networking',
                'description' => 'Enterprise networking workflow: cable trays, Cat6 termination, server rack dressing, managed switch VLANs, and ceiling Wi-Fi AP deployment.',
                'estimated_days' => 4,
                'phases' => [
                    [
                        'phase_name' => 'Phase 1: Pathway & Cable Tray Laying',
                        'tasks' => [
                            [
                                'title' => 'Cable Tray & Conduit Backbone Installation',
                                'description' => 'Install perforated cable trays along corridor ceilings and drop conduits to workstations.',
                                'priority' => 'medium',
                                'estimated_hours' => 6.0,
                                'checklists' => [
                                    'Fix ceiling threaded rods and tray supports every 1.2m',
                                    'Lay cable tray with grounding bridge jumpers',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 2: Structured Cat6 Cabling & Faceplates',
                        'tasks' => [
                            [
                                'title' => 'Cat6 UTP Pulling & Information Outlet (I/O) Crimping',
                                'description' => 'Pull dual network drops to desk locations and terminate on keystone jacks.',
                                'priority' => 'high',
                                'estimated_hours' => 8.0,
                                'checklists' => [
                                    'Pull structured drops bundled and comb-straightened',
                                    'Terminate keystone jacks with punch-down tool',
                                    'Fit modular faceplates on desk raceways with port labels',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 3: Server Rack Dressing & Patch Panels',
                        'tasks' => [
                            [
                                'title' => 'Patch Panel Punch Down & Cable Continuity Testing',
                                'description' => 'Terminate 24/48 port patch panels and test wiremap with network cable certifier.',
                                'priority' => 'high',
                                'estimated_hours' => 5.0,
                                'checklists' => [
                                    'Punch down all ports following T568B sequence',
                                    'Run cable continuity & pinout tester on 100% of nodes',
                                    'Fix numbering tags on patch panel fascia',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 4: Managed Switches & Wi-Fi Access Points',
                        'tasks' => [
                            [
                                'title' => 'VLAN Segmentation & Managed PoE Switch Configuration',
                                'description' => 'Configure Layer-2/3 switches with separate VLANs for Data, Voice, CCTV, and Guests.',
                                'priority' => 'urgent',
                                'estimated_hours' => 4.0,
                                'checklists' => [
                                    'Configure Management, Corporate Data, and Guest VLANs',
                                    'Enable Spanning Tree Protocol (RSTP) and loop protection',
                                    'Mount ceiling Wi-Fi 6 Access Points with PoE powering',
                                    'Configure unified SSID with WPA3 Enterprise security',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 5: Speed Testing & Port Map Documentation',
                        'tasks' => [
                            [
                                'title' => 'Bandwidth Throughput Testing & Handover Documentation',
                                'description' => 'Perform speed and latency tests at each desk node and provide laminated port mapping sheet.',
                                'priority' => 'medium',
                                'estimated_hours' => 2.5,
                                'checklists' => [
                                    'Test Gigabit throughput and ping latency on all drops',
                                    'Create laminated rack port-to-desk allocation map',
                                    'Handover network admin passwords to IT manager',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'software_web_app' => [
                'name' => 'Web Application & Custom Software Workflow',
                'category' => 'software_web',
                'description' => 'Full development lifecycle for webpage/portal deliverables: requirements sign-off, UI design, database architecture, testing, and production deployment.',
                'estimated_days' => 10,
                'phases' => [
                    [
                        'phase_name' => 'Phase 1: Architecture & Database Design',
                        'tasks' => [
                            [
                                'title' => 'Specification Finalization & ERD Schema Modeling',
                                'description' => 'Finalize functional requirements, entity relationships, and API contract specifications.',
                                'priority' => 'high',
                                'estimated_hours' => 6.0,
                                'checklists' => [
                                    'Write database schema migrations and relationships',
                                    'Define role-based permissions matrix',
                                    'Establish repository branches and CI/CD pipelines',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 2: Frontend & UI Engineering',
                        'tasks' => [
                            [
                                'title' => 'Responsive UI & Component System Development',
                                'description' => 'Implement responsive views, forms, modals, and dynamic data tables.',
                                'priority' => 'high',
                                'estimated_hours' => 14.0,
                                'checklists' => [
                                    'Build responsive desktop and mobile views',
                                    'Ensure dark mode & light mode contrast compliance',
                                    'Add client-side validation and responsive feedback',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 3: Backend Logic & Integrations',
                        'tasks' => [
                            [
                                'title' => 'Business Logic, REST APIs & Payment/SMS Gateways',
                                'description' => 'Develop core controller actions, notifications, and third-party API webhooks.',
                                'priority' => 'urgent',
                                'estimated_hours' => 16.0,
                                'checklists' => [
                                    'Implement CRUD controller actions and policy authorization',
                                    'Integrate payment gateway / WhatsApp SMS notifications',
                                    'Add audit logging for sensitive actions',
                                ],
                            ],
                        ],
                    ],
                    [
                        'phase_name' => 'Phase 4: QA & Production Deployment',
                        'tasks' => [
                            [
                                'title' => 'User Acceptance Testing, SSL & Live Go-Live',
                                'description' => 'Conduct end-to-end tests, configure production server with SSL, and go live.',
                                'priority' => 'urgent',
                                'estimated_hours' => 8.0,
                                'checklists' => [
                                    'Run automated test suite and manual regression checks',
                                    'Provision cloud VPS, Nginx, and automated SSL certificate',
                                    'Configure automated database daily backups',
                                    'Conduct client demo and handover administrator credentials',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get templates matching a given project type.
     */
    public static function getTemplatesForType(string $projectType): array
    {
        $all = static::getAllTemplates();
        $matched = [];

        foreach ($all as $key => $template) {
            if ($template['category'] === $projectType) {
                $matched[$key] = $template;
            }
        }

        // If no direct category match, or if project is 'hybrid', return all relevant hardware/software templates
        if (empty($matched) || $projectType === 'hybrid') {
            return $all;
        }

        return $matched;
    }

    /**
     * Apply a chosen workflow template to a project.
     * Generates all phases, tasks, and checklist items.
     */
    public static function applyTemplateToProject(Project $project, string $templateKey, ?int $defaultAssigneeId = null): int
    {
        $templates = static::getAllTemplates();
        if (!isset($templates[$templateKey])) {
            throw new \InvalidArgumentException("Template '{$templateKey}' not found.");
        }

        $template = $templates[$templateKey];
        $assigneeId = $defaultAssigneeId ?? $project->lead_technician_id ?? $project->assigned_to;
        $orderCounter = (int) $project->tasks()->max('order_column') + 1;
        $tasksCreatedCount = 0;

        DB::transaction(function () use ($project, $template, $templateKey, $assigneeId, &$orderCounter, &$tasksCreatedCount) {
            foreach ($template['phases'] as $phase) {
                $phaseName = $phase['phase_name'];

                foreach ($phase['tasks'] as $taskData) {
                    $task = ProjectTask::create([
                        'project_id' => $project->id,
                        'phase_name' => $phaseName,
                        'title' => $taskData['title'],
                        'description' => $taskData['description'] ?? null,
                        'assigned_to' => $assigneeId,
                        'priority' => $taskData['priority'] ?? 'medium',
                        'status' => 'pending',
                        'estimated_hours' => $taskData['estimated_hours'] ?? null,
                        'order_column' => $orderCounter++,
                    ]);

                    $checklistOrder = 1;
                    if (!empty($taskData['checklists'])) {
                        foreach ($taskData['checklists'] as $checkItem) {
                            ProjectTaskChecklist::create([
                                'project_task_id' => $task->id,
                                'item_text' => $checkItem,
                                'is_completed' => false,
                                'order_column' => $checklistOrder++,
                            ]);
                        }
                    }

                    $tasksCreatedCount++;
                }
            }

            // Create audit log
            ProjectAuditLog::create([
                'project_id' => $project->id,
                'auditable_type' => Project::class,
                'auditable_id' => $project->id,
                'user_id' => Auth::id() ?? $project->created_by,
                'action' => 'applied_workflow_template',
                'description' => "Applied pre-built workflow template '{$template['name']}' ({$tasksCreatedCount} tasks generated).",
                'changes' => [
                    'template' => $templateKey,
                    'tasks_count' => $tasksCreatedCount,
                ],
            ]);
        });

        return $tasksCreatedCount;
    }
}
