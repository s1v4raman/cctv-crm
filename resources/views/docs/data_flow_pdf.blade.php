<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CCTV CRM - Module Data Flow & Architecture Specification</title>
    <style>
        @page {
            margin: 28px 32px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
        }
        .page-break {
            page-break-after: always;
        }
        
        /* Header & Footer */
        .doc-header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 10px;
            font-weight: bold;
            color: #4f46e5;
            margin-top: 2px;
        }
        .doc-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Section Headings */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #ffffff;
            background-color: #1e293b;
            padding: 5px 10px;
            border-radius: 4px;
            margin-top: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subsection-title {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            border-left: 3px solid #4f46e5;
            padding-left: 6px;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        /* Diagram Boxes & Flowcharts */
        .diagram-container {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .flow-step {
            display: inline-block;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 9px;
            color: #1e293b;
            text-align: center;
            vertical-align: middle;
        }
        .flow-arrow {
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
            color: #6366f1;
            padding: 0 4px;
            vertical-align: middle;
        }
        .flow-highlight {
            background: #eef2ff;
            border-color: #818cf8;
            color: #3730a3;
        }
        .flow-success {
            background: #ecfdf5;
            border-color: #6ee7b7;
            color: #065f46;
        }
        .flow-warn {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        /* Tables */
        table.spec-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        table.spec-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
        }
        table.spec-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: top;
        }
        table.spec-table tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .table-col-key {
            font-weight: bold;
            color: #0f172a;
            width: 25%;
        }

        /* Key Metrics & Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-indigo { background: #e0e7ff; color: #3730a3; }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-amber  { background: #fef3c7; color: #92400e; }
        .badge-red    { background: #fee2e2; color: #991b1b; }

        .callout-box {
            background-color: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 8.5px;
            color: #1e40af;
        }
    </style>
</head>
<body>

    {{-- DOCUMENT HEADER --}}
    <div class="doc-header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="doc-title">CCTV CRM System Architecture</h1>
                    <div class="doc-subtitle">Enterprise Module Data Flow & Real-Time Processing Specification</div>
                    <div class="doc-meta">Document Version: 2.0 &middot; Generated: {{ now()->format('d M Y, h:i A') }} &middot; Environment: Production Ready</div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <span class="badge badge-indigo">Official System Spec</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- 1. MASTER ARCHITECTURAL DATA FLOW --}}
    <div class="section-title">1. Master System Data Flow Overview</div>
    <div class="diagram-container">
        <div style="text-align: center; margin-bottom: 8px;">
            <span class="flow-step flow-highlight">1. Customer Lead</span>
            <span class="flow-arrow">&rarr;</span>
            <span class="flow-step">2. Site Survey Blueprint</span>
            <span class="flow-arrow">&rarr;</span>
            <span class="flow-step">3. Quotation (BOQ)</span>
            <span class="flow-arrow">&rarr;</span>
            <span class="flow-step flow-success">4. Installation Job</span>
        </div>
        <div style="text-align: center; margin-bottom: 8px;">
            <span class="flow-step flow-warn">5. Vendor PO (Procurement)</span>
            <span class="flow-arrow">&rarr;</span>
            <span class="flow-step flow-success">6. Stock Inward (Warehouse)</span>
            <span class="flow-arrow">&rarr;</span>
            <span class="flow-step">7. Installed Equipment (Serial Asset)</span>
            <span class="flow-arrow">&rarr;</span>
            <span class="flow-step flow-highlight">8. Tax Invoice & Payment</span>
        </div>
        <div style="text-align: center;">
            <span class="flow-step">9. AMC Contracts & Preventive Visits</span>
            <span class="flow-arrow">&harr;</span>
            <span class="flow-step flow-warn">10. Support Tickets (4h-48h SLA)</span>
            <span class="flow-arrow">&harr;</span>
            <span class="flow-step flow-highlight">11. Field Technician Portal</span>
        </div>
    </div>

    {{-- 2. SALES PIPELINE & QUOTATIONS --}}
    <div class="section-title">2. Sales Pipeline, Surveys & Quotation Engine</div>
    <table class="spec-table">
        <thead>
            <tr>
                <th style="width: 20%;">Module</th>
                <th style="width: 25%;">Primary Tables</th>
                <th style="width: 25%;">Input & Triggers</th>
                <th style="width: 30%;">Data Processing & Output State</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="table-col-key">Leads Management</td>
                <td><code>leads</code><br><code>lead_histories</code></td>
                <td>Phone / WhatsApp / Web Inquiry / Walk-in</td>
                <td>Stages: <code>new</code> &rarr; <code>contacted</code> &rarr; <code>site_visit_scheduled</code> &rarr; <code>quoted</code> &rarr; <code>won/lost</code>. Immutable audit trail in <code>lead_histories</code>.</td>
            </tr>
            <tr>
                <td class="table-col-key">Site Survey Assessment</td>
                <td><code>site_surveys</code><br><code>site_survey_photos</code></td>
                <td>Technician Site Inspection</td>
                <td>Stores indoor/outdoor camera counts, conduit & cable meters, NVR storage retention days, and physical site blueprint photos.</td>
            </tr>
            <tr>
                <td class="table-col-key">Quotation (BOQ) Engine</td>
                <td><code>quotations</code><br><code>quotation_items</code></td>
                <td>Survey specs + Product catalog</td>
                <td>Auto-calculates item totals (<code>qty &times; price</code>), subtotal, discount deductions, 18% GST tax, and grand total. Generates <code>QUO-YYYYMMDD-XXXX</code>.</td>
            </tr>
        </tbody>
    </table>

    {{-- 3. FIELD OPERATIONS & SERVICE --}}
    <div class="section-title">3. Field Operations, Installation & Helpdesk SLA</div>
    <table class="spec-table">
        <thead>
            <tr>
                <th style="width: 20%;">Module</th>
                <th style="width: 25%;">Primary Tables</th>
                <th style="width: 25%;">Input & Triggers</th>
                <th style="width: 30%;">Data Processing & Output State</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="table-col-key">Installation Jobs</td>
                <td><code>installation_jobs</code></td>
                <td>Approved Quotation (Quote Won)</td>
                <td>Dispatches technician; tracks hardware mounting, wiring, IP configuration. Status: <code>pending</code> &rarr; <code>assigned</code> &rarr; <code>in_progress</code> &rarr; <code>completed</code>.</td>
            </tr>
            <tr>
                <td class="table-col-key">Installed Equipment Registry</td>
                <td><code>installed_equipment</code></td>
                <td>Job Completion Handover</td>
                <td>Registers hardware serial numbers, static IP addresses, camera locations (e.g. 'Main Gate'), installation date, warranty calculation.</td>
            </tr>
            <tr>
                <td class="table-col-key">AMC Contracts & Visits</td>
                <td><code>amc_contracts</code><br><code>amc_visits</code></td>
                <td>Customer Service Agreement</td>
                <td>Generates contract (<code>AMC-XXXX</code>) and auto-schedules preventive maintenance visits (e.g. 4 quarterly visits). Tech records lens cleaning & UPS test.</td>
            </tr>
            <tr>
                <td class="table-col-key">Support Tickets (SLA)</td>
                <td><code>service_tickets</code></td>
                <td>Client Fault / Offline Camera</td>
                <td>Priorities: Critical (4h), High (12h), Medium (24h), Low (48h). Auto-links AMC/Warranty coverage. Tech logs troubleshooting & replaced parts.</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    {{-- 4. INVENTORY, PROCUREMENT & STOCK LEDGER --}}
    <div class="doc-header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h2 class="doc-title" style="font-size: 14px;">CCTV CRM Architecture (Page 2)</h2>
                    <div class="doc-subtitle">Inventory, Procurement, Billing & Database Entity Relations</div>
                </td>
                <td style="text-align: right;">
                    <span class="badge badge-green">Validated</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">4. Warehouse Inventory, Procurement & Inwarding Ledger</div>
    <div class="callout-box">
        <strong>Automatic Inwarding Guarantee:</strong> Marking a Purchase Order (PO) item received increments physical stock in <code>products.stock_quantity</code> and writes an immutable audit record in <code>stock_movements</code> with type <code>'in'</code>.
    </div>

    <table class="spec-table">
        <thead>
            <tr>
                <th style="width: 20%;">Module</th>
                <th style="width: 25%;">Primary Tables</th>
                <th style="width: 25%;">Input & Triggers</th>
                <th style="width: 30%;">Data Processing & Output State</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="table-col-key">Suppliers & Vendors</td>
                <td><code>suppliers</code></td>
                <td>Distributor Registration</td>
                <td>Stores vendor contacts, GSTIN, payment terms (Net 30, Advance), and aggregates cumulative purchase volume.</td>
            </tr>
            <tr>
                <td class="table-col-key">Purchase Orders (PO)</td>
                <td><code>purchase_orders</code><br><code>purchase_order_items</code></td>
                <td>Stock Replenishment Order</td>
                <td>Generates <code>PO-YYYYMMDD-XXXX</code>. Calculates unit cost, subtotal, 18% GST tax, shipping. Status: <code>draft</code> &rarr; <code>ordered</code> &rarr; <code>received</code>.</td>
            </tr>
            <tr>
                <td class="table-col-key">Warehouse Stock & Movements</td>
                <td><code>products</code><br><code>stock_movements</code></td>
                <td>PO Inward / Job Consumption</td>
                <td>Real-time inventory levels, minimum stock alerts. Logs every stock movement (<code>type</code>, <code>quantity</code>, <code>balance_after</code>, <code>user_id</code>).</td>
            </tr>
        </tbody>
    </table>

    {{-- 5. BILLING, GST INVOICING & PAYMENTS --}}
    <div class="section-title">5. Invoicing, GST Billing & Payment Collection</div>
    <table class="spec-table">
        <thead>
            <tr>
                <th style="width: 20%;">Module</th>
                <th style="width: 25%;">Primary Tables</th>
                <th style="width: 25%;">Input & Triggers</th>
                <th style="width: 30%;">Data Processing & Output State</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="table-col-key">GST Tax Invoices</td>
                <td><code>invoices</code></td>
                <td>Completed Installation Job</td>
                <td>Generates <code>INV-YYYYMMDD-XXXX</code> with itemized hardware line items, taxable amount, 18% GST, due date, and payment instructions.</td>
            </tr>
            <tr>
                <td class="table-col-key">Payments Ledger</td>
                <td><code>payments</code></td>
                <td>Advance / Settlement Receipts</td>
                <td>Records transaction (Cash, Bank Transfer, UPI, Cheque, Reference No). Automatically recalculates <code>amount_paid</code>, <code>balanceDue()</code>, and status (<code>unpaid</code>, <code>partially_paid</code>, <code>paid</code>, <code>overdue</code>).</td>
            </tr>
        </tbody>
    </table>

    {{-- 6. SECURITY, ROLES & PERMISSIONS MATRIX --}}
    <div class="section-title">6. Role-Based Access Control (RBAC) & Security Matrix</div>
    <table class="spec-table">
        <thead>
            <tr>
                <th>System Module / Feature</th>
                <th style="text-align: center;">Administrator</th>
                <th style="text-align: center;">Staff / Sales</th>
                <th style="text-align: center;">Technician</th>
                <th style="text-align: center;">Customer</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Leads & Quotations Management</td>
                <td style="text-align: center;"><span class="badge badge-green">Full CRUD</span></td>
                <td style="text-align: center;"><span class="badge badge-green">View & Quote</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
            </tr>
            <tr>
                <td>Field Work (Jobs, Tickets, AMC Visits)</td>
                <td style="text-align: center;"><span class="badge badge-green">Full CRUD</span></td>
                <td style="text-align: center;"><span class="badge badge-green">Assign & View</span></td>
                <td style="text-align: center;"><span class="badge badge-indigo">Assigned Only</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
            </tr>
            <tr>
                <td>Invoicing & Financial Ledgers</td>
                <td style="text-align: center;"><span class="badge badge-green">Full CRUD</span></td>
                <td style="text-align: center;"><span class="badge badge-green">Create & Pay</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
            </tr>
            <tr>
                <td>Inventory & Vendor Procurement (POs)</td>
                <td style="text-align: center;"><span class="badge badge-green">Full CRUD</span></td>
                <td style="text-align: center;"><span class="badge badge-green">Create & Inward</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
            </tr>
            <tr>
                <td>User Accounts & Role Permissions</td>
                <td style="text-align: center;"><span class="badge badge-green">Full CRUD</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
                <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 15px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px;">
        CCTV CRM Enterprise Edition &middot; Confidential & Proprietary &middot; Designed for High-Reliability Field Operations
    </div>

</body>
</html>
