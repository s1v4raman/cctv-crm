<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin to Technician Complete Project Workflow Guide</title>
    <style>
        @page {
            margin: 24px 28px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            line-height: 1.45;
            color: #1e293b;
            background: #ffffff;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Header & Branding */
        .doc-header {
            border-bottom: 2.5px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .doc-title {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.2px;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 9.5px;
            font-weight: 700;
            color: #2563eb;
            margin-top: 2px;
        }
        .doc-meta {
            font-size: 8.5px;
            color: #64748b;
            text-align: right;
        }

        /* Flow Visual Step Cards */
        .flow-step-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .flow-step {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
            width: 16.6%;
            vertical-align: top;
        }
        .flow-step.active {
            background: #eff6ff;
            border-color: #3b82f6;
        }
        .flow-step-num {
            font-size: 8px;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
        }
        .flow-step-title {
            font-size: 9px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 1px;
        }
        .flow-step-role {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 1px;
        }

        /* Section Headings */
        .section-header {
            background: #0f172a;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .section-header.blue {
            background: #1e40af;
        }
        .section-header.emerald {
            background: #065f46;
        }

        /* Tables & Spec Layouts */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5px;
        }
        .data-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
        }
        .data-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        /* Code/Value Pill & Badges */
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 7.5px;
            text-transform: uppercase;
        }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-emerald { background: #dcfce7; color: #166534; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-rose { background: #ffe4e6; color: #9f1239; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        .badge-slate { background: #e2e8f0; color: #334155; }

        .code-box {
            background: #0f172a;
            color: #38bdf8;
            font-family: 'Courier New', Courier, monospace;
            font-size: 8px;
            padding: 6px 8px;
            border-radius: 4px;
            margin: 4px 0 8px 0;
            line-height: 1.35;
        }

        .highlight-box {
            background: #f0fdf4;
            border-left: 3px solid #16a34a;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 8.5px;
        }
        .privacy-box {
            background: #fffbeb;
            border-left: 3px solid #d97706;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 8.5px;
        }

        /* Mini checklist preview */
        .checklist-item {
            margin: 2px 0;
            padding-left: 12px;
            position: relative;
        }
        .checklist-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #16a34a;
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- DOCUMENT HEADER --}}
    <div class="doc-header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="doc-title">Precision IT Systems • Operations Manual</div>
                    <div class="doc-subtitle">Admin-to-Technician Project Workflow, Verification & Completion Guide</div>
                </td>
                <td class="doc-meta">
                    <strong>Version:</strong> 2.4 (Production)<br>
                    <strong>Updated:</strong> {{ date('d M Y') }}<br>
                    <strong>Modules:</strong> Projects, Calendar, Tech Portal, Wages
                </td>
            </tr>
        </table>
    </div>

    {{-- 6-STAGE VISUAL ROADMAP --}}
    <table class="flow-step-bar">
        <tr>
            <td class="flow-step">
                <div class="flow-step-num">Step 1</div>
                <div class="flow-step-title">Create Project</div>
                <div class="flow-step-role">Admin / PM</div>
            </td>
            <td class="flow-step">
                <div class="flow-step-num">Step 2</div>
                <div class="flow-step-title">Approve & Rate</div>
                <div class="flow-step-role">Admin</div>
            </td>
            <td class="flow-step">
                <div class="flow-step-num">Step 3</div>
                <div class="flow-step-title">Delegate Tasks</div>
                <div class="flow-step-role">Admin / PM</div>
            </td>
            <td class="flow-step active">
                <div class="flow-step-num">Step 4</div>
                <div class="flow-step-title">Accept / Reject</div>
                <div class="flow-step-role">Technician</div>
            </td>
            <td class="flow-step">
                <div class="flow-step-num">Step 5</div>
                <div class="flow-step-title">Execute & Proof</div>
                <div class="flow-step-role">Technician</div>
            </td>
            <td class="flow-step">
                <div class="flow-step-num">Step 6</div>
                <div class="flow-step-title">Verify & Pay</div>
                <div class="flow-step-role">Admin</div>
            </td>
        </tr>
    </table>

    {{-- STAGE 1: CREATION --}}
    <div class="section-header">Stage 1: Project Initialization & BOM Builder (Admin)</div>
    <table class="data-table">
        <tr>
            <th style="width: 20%;">Attribute</th>
            <th style="width: 40%;">Technical Details & Routing</th>
            <th style="width: 40%;">Example Live Payload / Values</th>
        </tr>
        <tr>
            <td><strong>Action & Route</strong></td>
            <td><code>POST /projects</code> (or wizard: <code>POST /projects/wizard</code>)<br>Method: <code>ProjectController::store()</code></td>
            <td>Creates new master installation project record</td>
        </tr>
        <tr>
            <td><strong>Core Fields</strong></td>
            <td>
                • Auto-sequential Project Code generator<br>
                • Type: CCTV, Attendance, Networking, Hybrid<br>
                • Assigns Lead Technician (FK: <code>users.id</code>)
            </td>
            <td>
                <code>project_code</code>: <strong>PRJ-2026-0002</strong><br>
                <code>title</code>: <strong>A.C.S. Medical College - CCTV & Access</strong><br>
                <code>lead_technician_id</code>: <strong>3 (Bob Miller)</strong><br>
                <code>budget</code>: <strong>₹1,50,000.00</strong>
            </td>
        </tr>
        <tr>
            <td><strong>BOM (Bill of Materials)</strong></td>
            <td>
                Inline array in <code>ProjectMaterial</code> table.<br>
                Auto-learns products and vendors by normalized name key.
            </td>
            <td>
                • 16x Hikvision 4MP Dome Camera (Warehouse)<br>
                • 3x Cat6 UTP 305m Cable Bundle (Warehouse)<br>
                • 1x 16-Channel 4K NVR + 4TB Surveillance HDD
            </td>
        </tr>
        <tr>
            <td><strong>Initial State</strong></td>
            <td>Sets <code>status = 'pending_approval'</code> (or <code>approved</code> if created directly by Admin with company).</td>
            <td><span class="badge badge-amber">pending_approval</span> → Logs in <code>ProjectAuditLog</code></td>
        </tr>
    </table>

    {{-- STAGE 2: APPROVAL & RATES --}}
    <div class="section-header blue">Stage 2: Admin Approval & Per-Metre Cabling Rate Setup</div>
    <table class="data-table">
        <tr>
            <th style="width: 20%;">Item</th>
            <th style="width: 40%;">Backend Logic</th>
            <th style="width: 40%;">Financial / Operational Impact</th>
        </tr>
        <tr>
            <td><strong>Action & Route</strong></td>
            <td><code>POST /projects/{project}/approve</code><br>Method: <code>ProjectController::approve()</code></td>
            <td>Restricted strictly to administrators (<code>abort_unless(isAdmin(), 403)</code>)</td>
        </tr>
        <tr>
            <td><strong>Parameters</strong></td>
            <td>
                • <code>company_id</code>: Executing client entity<br>
                • <code>per_metre_rate</code>: Baseline cabling payout rate<br>
                • <code>approved_value</code>: Agreed budget ceiling<br>
                • <code>po_number</code>: Client Purchase Order
            </td>
            <td>
                <code>company_id</code>: <strong>2 (ACS Medical College)</strong><br>
                <code>per_metre_rate</code>: <strong>₹12.00 / metre</strong><br>
                <code>approved_value</code>: <strong>₹1,45,000.00</strong><br>
                <code>po_number</code>: <strong>PO-2026-MED-89</strong>
            </td>
        </tr>
        <tr>
            <td><strong>State Transition</strong></td>
            <td><code>status = 'approved'</code>, <code>approved_by = Admin ID</code>, <code>approved_on = now()</code>.</td>
            <td><span class="badge badge-blue">approved</span> (Project now eligible for active task dispatching)</td>
        </tr>
    </table>

    {{-- STAGE 3: TASK DELEGATION --}}
    <div class="section-header">Stage 3: Task Delegation & Checklist Generation (Admin / PM)</div>
    <table class="data-table">
        <tr>
            <th style="width: 20%;">Step</th>
            <th style="width: 40%;">Method & Routing</th>
            <th style="width: 40%;">Output Data & Auto-Notification</th>
        </tr>
        <tr>
            <td><strong>A. Workflow Template</strong></td>
            <td>
                <code>POST /projects/{project}/tasks/apply-template</code><br>
                <code>ProjectTaskController::applyTemplate()</code>
            </td>
            <td>Auto-generates standard CCTV installation phases (Conduit, Cabling, Mount, IP Config, Testing).</td>
        </tr>
        <tr>
            <td><strong>B. Custom Task Creation</strong></td>
            <td>
                <code>POST /calendar/tasks</code> or <code>POST /projects/{project}/tasks</code><br>
                <code>CalendarController::storeTask()</code>
            </td>
            <td>
                <code>title</code>: <strong>Pull 110m Cat6 Cable & Mount Cams 1-4</strong><br>
                <code>assigned_to</code>: <strong>3 (Bob Miller)</strong> | <code>priority</code>: <strong>urgent</strong><br>
                <code>shift</code>: <strong>Day Shift</strong> | <code>due_date</code>: <strong>2026-10-27</strong>
            </td>
        </tr>
        <tr>
            <td><strong>C. Checklist Creation</strong></td>
            <td>Inline array or <code>POST /project-tasks/{task}/checklists</code></td>
            <td>
                1. Lay conduit along 2nd-floor ceiling<br>
                2. Pull 110m Cat6 bundle from rack to corridor<br>
                3. Terminate RJ45 keystones & test wiremap<br>
                4. Securely mount 4 dome cameras
            </td>
        </tr>
        <tr>
            <td><strong>D. Alert Notification</strong></td>
            <td><code>NotificationLog::create(['channel' => 'whatsapp', ...])</code></td>
            <td>Dispatches dashboard bell notification and generates formatted WhatsApp task briefing link.</td>
        </tr>
    </table>

    <div class="page-break"></div>

    {{-- STAGE 4: TECHNICIAN ACCEPTANCE --}}
    <div class="section-header blue">Stage 4: Technician Inspection, Acceptance & Rejection Flow</div>
    
    <div class="privacy-box">
        <strong>🔒 Privacy & Data Sanitization (Server-Side Enforced):</strong><br>
        When a Technician or Worker opens their tasks via <code>CalendarController::index(['tab' => 'my_tasks'])</code>, the server passes task models through <code>sanitizeTaskForWorker()</code>. Sensitive client financial margins, master project budgets, vendor procurement rates, and BOM cost structures are <strong>completely stripped out</strong>. Technicians see only technical scope, date, shift, location, and checklists.
    </div>

    <table class="data-table">
        <tr>
            <th style="width: 22%;">Technician Action</th>
            <th style="width: 40%;">Code Execution & Route</th>
            <th style="width: 38%;">Database Updates & Audit Log</th>
        </tr>
        <tr>
            <td><strong>Path A: Accept Task<br>(Normal Flow)</strong></td>
            <td>
                <code>POST /calendar/tasks/{task}/accept</code><br>
                Method: <code>CalendarController::acceptTask()</code><br>
                Security check: <code>$task->assigned_to === Auth::id()</code>
            </td>
            <td>
                • <code>status = 'in_progress'</code><br>
                • <code>accepted_at = now()</code><br>
                • Clears any previous rejection notes<br>
                • Audit: <em>"Bob Miller accepted task 'Pull 110m Cat6...'"</em><br>
                <span class="badge badge-blue">in_progress</span>
            </td>
        </tr>
        <tr>
            <td><strong>Path B: Reject Task<br>(Site Inaccessible)</strong></td>
            <td>
                <code>POST /calendar/tasks/{task}/reject</code><br>
                Method: <code>CalendarController::rejectTask()</code><br>
                Requires reason (min 3 chars).
            </td>
            <td>
                • <code>status = 'rejected'</code><br>
                • <code>rejection_reason = "Site locked; painting in progress"</code><br>
                • <code>rejected_at = now()</code>, <code>rejected_by = Auth::id()</code><br>
                • Routes task back to Admin with warning badge for reassignment.<br>
                <span class="badge badge-rose">rejected</span>
            </td>
        </tr>
    </table>

    {{-- STAGE 5: FIELD EXECUTION & PROOF --}}
    <div class="section-header emerald">Stage 5: Field Execution, Checklist Ticking & Photographic Verification</div>
    <table class="data-table">
        <tr>
            <th style="width: 22%;">Field Activity</th>
            <th style="width: 40%;">Backend Route & Controller</th>
            <th style="width: 38%;">Verification Artifacts Recorded</th>
        </tr>
        <tr>
            <td><strong>1. Interactive Checklist</strong></td>
            <td>
                <code>POST /project-tasks/checklists/{checklist}/toggle</code><br>
                <code>ProjectTaskController::toggleChecklist()</code>
            </td>
            <td>
                • Toggles <code>is_completed = true/false</code><br>
                • Recalculates <code>task.checklist_progress</code> (e.g. 50% → 75% → 100%)
            </td>
        </tr>
        <tr>
            <td><strong>2. Punch-in Selfie & Output Proof</strong></td>
            <td>
                <code>POST /projects/{project}/work-days/{workDay}/attendances</code><br>
                <code>WorkDayController::storeAttendance()</code>
            </td>
            <td>
                • <code>photo_path</code>: Live webcam/phone punch selfie<br>
                • <code>cabling_metres</code>: <strong>110.0 m</strong><br>
                • <code>cameras_installed</code>: <strong>4 cameras</strong><br>
                • <code>attendance_type</code>: <strong>Full Day (₹900.00 base)</strong>
            </td>
        </tr>
        <tr>
            <td><strong>3. Material Consumption</strong></td>
            <td>
                <code>POST /projects/{project}/materials/consume</code><br>
                <code>ProjectMaterialController::consume()</code>
            </td>
            <td>
                Deducts physical stock from project BOM and creates <code>TaskMaterialConsumption</code> ledger entry.
            </td>
        </tr>
        <tr>
            <td><strong>4. Site Blockers (If Any)</strong></td>
            <td>
                <code>POST /projects/{project}/tasks/{task}/flag-blocker</code><br>
                <code>ProjectTaskController::flagBlocker()</code>
            </td>
            <td>
                Marks <code>status = 'blocked'</code>, logs reason, and fires automated WhatsApp alert to project managers.
            </td>
        </tr>
    </table>

    {{-- STAGE 6: VERIFICATION & COMPLETION --}}
    <div class="section-header">Stage 6: Admin Review, Done Verification & Wage Settlement</div>
    <div class="highlight-box">
        <strong>✅ 3-Point Admin Verification Checklist Before Sign-Off:</strong><br>
        1. <strong>Task Board Audit:</strong> All project tasks must reach <code>completed</code> status (<code>taskCompletionPercentage == 100%</code>).<br>
        2. <strong>Visual Proof Inspection:</strong> Admin opens the <strong>Shifts & Photos Lightbox</strong> to inspect the technician's check-in punch selfie.<br>
        3. <strong>Material & Output Reconciliation:</strong> Verified cabling metres pulled (110m) matches warehouse Cat6 cable depletion.
    </div>

    <table class="data-table">
        <tr>
            <th style="width: 25%;">Final Sign-off Step</th>
            <th style="width: 35%;">Action & Route</th>
            <th style="width: 40%;">Outcome & Data Results</th>
        </tr>
        <tr>
            <td><strong>A. Mark Task Done</strong></td>
            <td><code>PATCH /projects/{project}/tasks/{task}/status</code><br><code>ProjectTaskController::updateStatus()</code></td>
            <td>Sets <code>status = 'completed'</code>, <code>completed_at = now()</code>, <code>completed_by = Auth::id()</code>.</td>
        </tr>
        <tr>
            <td><strong>B. Project Closeout</strong></td>
            <td><code>PATCH /projects/{project}/status</code><br><code>ProjectController::updateStatus()</code></td>
            <td>Sets <code>status = 'completed'</code>, <code>progress_percentage = 100</code>, <code>completed_at = today()</code>.</td>
        </tr>
        <tr>
            <td><strong>C. Automated Wage Payout</strong></td>
            <td><code>POST /wages/settle</code><br><code>WageController::settle()</code></td>
            <td>
                Formula: <code>(Daily Shift Base: ₹900) + (110m Cabling @ ₹12: ₹1,320) - (Advances) = Net ₹2,220.00</code><br>
                Disbursed via Cash or UPI and marked <span class="badge badge-emerald">✓ Settled</span> in Weekly Wages.
            </td>
        </tr>
    </table>

    {{-- SUMMARY QUICK-REFERENCE TABLE --}}
    <div class="section-header blue">Developer & Operational Quick-Reference Cheat Sheet</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Operation</th>
                <th>HTTP Route</th>
                <th>Controller & Method</th>
                <th>Authorized Role</th>
                <th>Resulting Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Create Project</td>
                <td><code>POST /projects</code></td>
                <td><code>ProjectController@store</code></td>
                <td>Admin, Staff</td>
                <td><span class="badge badge-amber">pending_approval</span></td>
            </tr>
            <tr>
                <td>Approve Project</td>
                <td><code>POST /projects/{id}/approve</code></td>
                <td><code>ProjectController@approve</code></td>
                <td>Admin Only</td>
                <td><span class="badge badge-blue">approved</span></td>
            </tr>
            <tr>
                <td>Create Project Task</td>
                <td><code>POST /calendar/tasks</code></td>
                <td><code>CalendarController@storeTask</code></td>
                <td>Admin, PM</td>
                <td><span class="badge badge-slate">pending</span></td>
            </tr>
            <tr>
                <td>Accept Task</td>
                <td><code>POST /calendar/tasks/{id}/accept</code></td>
                <td><code>CalendarController@acceptTask</code></td>
                <td>Assigned Tech</td>
                <td><span class="badge badge-blue">in_progress</span></td>
            </tr>
            <tr>
                <td>Reject Task</td>
                <td><code>POST /calendar/tasks/{id}/reject</code></td>
                <td><code>CalendarController@rejectTask</code></td>
                <td>Assigned Tech</td>
                <td><span class="badge badge-rose">rejected</span></td>
            </tr>
            <tr>
                <td>Toggle Checklist</td>
                <td><code>POST /project-tasks/checklists/{id}/toggle</code></td>
                <td><code>ProjectTaskController@toggleChecklist</code></td>
                <td>Assigned Tech</td>
                <td><code>is_completed = true</code></td>
            </tr>
            <tr>
                <td>Complete Task</td>
                <td><code>PATCH /projects/{id}/tasks/{task}/status</code></td>
                <td><code>ProjectTaskController@updateStatus</code></td>
                <td>Tech / Admin</td>
                <td><span class="badge badge-emerald">completed</span></td>
            </tr>
            <tr>
                <td>Close Project</td>
                <td><code>PATCH /projects/{id}/status</code></td>
                <td><code>ProjectController@updateStatus</code></td>
                <td>Admin Only</td>
                <td><span class="badge badge-emerald">completed (100%)</span></td>
            </tr>
            <tr>
                <td>Settle Wages</td>
                <td><code>POST /wages/settle</code></td>
                <td><code>WageController@settle</code></td>
                <td>Admin Only</td>
                <td><span class="badge badge-emerald">✓ Settled</span></td>
            </tr>
        </tbody>
    </table>

</body>
</html>
