<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CCTV CRM - Manual Security Verification Guide</title>
    <style>
        @page {
            margin: 28px 32px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            line-height: 1.35;
            color: #1e293b;
            background: #ffffff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-sub {
            font-size: 8.5px;
            color: #2563eb;
            font-weight: bold;
            margin-top: 2px;
        }
        .header-meta {
            font-size: 7.5px;
            color: #64748b;
            text-align: right;
            line-height: 1.4;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            background: #f1f5f9;
            border-left: 4px solid #2563eb;
            padding: 4px 8px;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        .sub-title {
            font-size: 9px;
            font-weight: bold;
            color: #1e40af;
            margin-top: 6px;
            margin-bottom: 3px;
        }
        p {
            margin: 0 0 5px 0;
        }
        ul, ol {
            margin: 0 0 6px 16px;
            padding: 0;
        }
        li {
            margin-bottom: 2.5px;
        }
        .highlight-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 8px;
        }
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-blue { background: #dbeafe; color: #1e40af; border: 0.5px solid #93c5fd; }
        .badge-red { background: #fee2e2; color: #991b1b; border: 0.5px solid #fca5a5; }
        .badge-green { background: #dcfce7; color: #166534; border: 0.5px solid #86efac; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; border: 0.5px solid #d8b4fe; }
        
        .code-box {
            font-family: 'Courier New', Courier, monospace;
            font-size: 7.5px;
            background: #0f172a;
            color: #f8fafc;
            padding: 4px 6px;
            border-radius: 3px;
            margin: 3px 0 5px 0;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .code-inline {
            font-family: 'Courier New', Courier, monospace;
            background: #f1f5f9;
            color: #0f172a;
            padding: 1px 3px;
            border-radius: 2px;
            border: 0.5px solid #cbd5e1;
        }
        .result-box {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 4px 6px;
            margin: 3px 0 6px 0;
            font-size: 7.8px;
        }
        .warning-box {
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 4px 6px;
            margin: 3px 0 6px 0;
            font-size: 7.8px;
        }
        table.matrix {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 10px;
        }
        table.matrix th {
            background: #0f172a;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 5px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        table.matrix td {
            font-size: 7.5px;
            padding: 3.5px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        table.matrix tr:nth-child(even) {
            background: #f8fafc;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            margin-top: 12px;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            font-size: 7px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Block -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="header-title">Precision IT Systems &bull; CCTV Enterprise CRM</div>
                <div class="header-sub">Manual Security Verification & Data Store Audit Standard</div>
                <div style="font-size: 7.5px; color: #475569; margin-top: 2px;">
                    Comprehensive Manual Testing Process for Multi-Role Access Control & Data Protection
                </div>
            </td>
            <td class="header-meta" style="width: 30%;">
                <strong>Document ID:</strong> SEC-MAN-2026-V1<br>
                <strong>Audit Type:</strong> 100% Manual Hands-On Testing<br>
                <strong>Framework:</strong> Laravel 11 / PHP 8.3+ Enterprise<br>
                <strong>Release Build:</strong> ERP v2.6 Ready
            </td>
        </tr>
    </table>

    <!-- Project Overview & Security Model -->
    <div class="section-title">1. Project Roles & Data Stores Scope</div>
    
    <div class="highlight-box">
        <strong>Application Security Architecture:</strong>
        <table style="width: 100%; margin-top: 4px; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                    <strong>Active User Roles & Boundaries:</strong>
                    <ul style="margin-top: 2px;">
                        <li><span class="badge badge-purple">Admin</span> Full privileges across Finance, GST, Payroll, Users & Config.</li>
                        <li><span class="badge badge-blue">Staff</span> Internal operations, sales leads, inventories & quotations.</li>
                        <li><span class="badge badge-blue">Technician</span> Field jobs, daily site logs, attendance & assigned tickets.</li>
                        <li><span class="badge badge-green">Customer</span> Isolated client portal: view own quotations, tickets & invoices.</li>
                        <li><span class="badge badge-red">Guest</span> Unauthenticated public visitor: storefront, public view links only.</li>
                    </ul>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Evaluated Data Stores:</strong>
                    <ul style="margin-top: 2px;">
                        <li><strong>Relational DB:</strong> SQLite/MySQL (Users, Passwords, Ledger, Financials, Sites).</li>
                        <li><strong>File Disks:</strong> Local private, public disk, Cloudflare R2 / AWS S3 buckets.</li>
                        <li><strong>Session/Cache:</strong> Encrypted cookie sessions, DB session store, CSRF tokens.</li>
                        <li><strong>Secrets Store:</strong> Root <span class="code-inline">.env</span>, Razorpay API keys, DB credentials, APP_KEY.</li>
                        <li><strong>Logs Store:</strong> <span class="code-inline">storage/logs/laravel.log</span>, ProjectAuditLog database table.</li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>

    <!-- Phase 1 -->
    <div class="section-title">Phase 1: Multi-Role Authorization & Access Control (Manual Tests)</div>

    <div class="sub-title">Test 1.1: Vertical Privilege Escalation (Low Role accessing High Role)</div>
    <p><strong>Objective:</strong> Verify that Customers, Technicians, and Staff cannot access restricted Administrator or Finance modules.</p>
    <ol>
        <li>Open two distinct browser windows:
            <ul>
                <li><strong>Window A (Normal Browser):</strong> Log in as an <span class="badge badge-purple">Admin</span> user.</li>
                <li><strong>Window B (Incognito / Private Window):</strong> Log in as a <span class="badge badge-blue">Technician</span> or <span class="badge badge-blue">Staff</span> user.</li>
            </ul>
        </li>
        <li>In Window A, navigate to sensitive administrative management routes:
            <br><span class="code-inline">/finance</span>, <span class="code-inline">/wage</span>, <span class="code-inline">/gst-compliance</span>, <span class="code-inline">/admin/users</span>
        </li>
        <li>Copy the full URL from Window A and paste it into Window B (authenticated as Technician/Staff). Press Enter.</li>
        <li><strong>Document Deletion Sub-test:</strong> In Window B, inspect a project document delete button (<span class="code-inline">DELETE /projects/documents/{document}</span>). Attempt submitting the deletion form directly as non-admin.</li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> The application must respond with <strong>HTTP 403 Forbidden</strong> or redirect with an <em>"Unauthorized Access"</em> flash message. It must never render payroll records, GST balances, or execute administrative deletions.
    </div>

    <div class="sub-title">Test 1.2: Horizontal Privilege Escalation & IDOR (Customer A vs Customer B)</div>
    <p><strong>Objective:</strong> Ensure Customer A cannot view or tamper with Customer B's tickets, quotations, or confidential site documents by manipulating numeric identifiers.</p>
    <ol>
        <li>Create two test customer accounts: <strong>Customer 1</strong> (ID 10) and <strong>Customer 2</strong> (ID 11).</li>
        <li>Log in as Customer 1 in Browser 1. Create a service ticket or view an existing quotation. Note the record ID (e.g., Quotation <span class="code-inline">#105</span>).</li>
        <li>Log in as Customer 2 in Browser 2. Manually alter the URL path in Browser 2:
            <br><span class="code-inline">/customer/tickets/105</span>, <span class="code-inline">/customer/quotations/105</span>, <span class="code-inline">/projects/documents/{id_of_customer_1}/view</span>
        </li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> The server must deny access with <strong>HTTP 403 Forbidden</strong> or return <strong>HTTP 404 Not Found</strong> (scope restriction). Customer 2 must never see Customer 1's pricing, location address, or blueprints.
    </div>

    <div class="sub-title">Test 1.3: Unauthenticated Access to Public Endpoints</div>
    <p><strong>Objective:</strong> Verify guest boundaries on public quotation links and payment webhooks.</p>
    <ol>
        <li>Open an Incognito window without signing in. Attempt navigating to internal endpoints:
            <br><span class="code-inline">/dashboard</span>, <span class="code-inline">/projects</span>, <span class="code-inline">/leads</span>, <span class="code-inline">/documents/to-invoice</span>.
            <br><em>Expected:</em> Immediate redirect to <span class="code-inline">/login</span>.
        </li>
        <li>Navigate to intentionally public links:
            <br><span class="code-inline">/view-quotation/{quotation}</span>, <span class="code-inline">/view-jcr/{jobCompletionReport}</span>, <span class="code-inline">/pay/invoice/{invoice}</span>.
        </li>
        <li>Press <span class="code-inline">Ctrl + U</span> to inspect the raw HTML source code.</li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> Public pages render customer-facing data only. Internal staff remarks, technician phone numbers, supplier purchase prices, or trade margins must <strong>never</strong> appear in the DOM or JSON comments.
    </div>

    <div class="page-break"></div>

    <!-- Phase 2 -->
    <div class="section-title">Phase 2: Data Store Security Verification</div>

    <div class="sub-title">Test 2.1: Relational Database Security (Passwords, Sensitive Columns, SQLi)</div>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 6px;">
                <strong>Step A: Verify Password Hashing</strong>
                <p>Open SQLite/MySQL client and query user credentials:</p>
                <div class="code-box">SELECT id, name, email, password FROM users LIMIT 5;</div>
                <div class="result-box">
                    <strong>Expected:</strong> Hashes must begin with <span class="code-inline">$2y$</span> or <span class="code-inline">$2a$</span> (Bcrypt 12+ rounds). No plain text, MD5, or SHA-1 hashes permitted.
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 6px;">
                <strong>Step B: Hidden Attributes in API / JSON</strong>
                <p>Open DevTools Network tab on user lookup / search endpoints.</p>
                <div class="result-box">
                    <strong>Expected:</strong> Password hashes and <span class="code-inline">remember_token</span> must be completely omitted from JSON outputs via <span class="code-inline">$hidden</span> array in User model.
                </div>
            </td>
        </tr>
    </table>

    <p style="margin-top: 4px;"><strong>Step C: Manual SQL Injection Probe on Search Filters</strong></p>
    <ol>
        <li>Navigate to list interfaces containing search parameters: <span class="code-inline">/projects</span>, <span class="code-inline">/leads</span>, <span class="code-inline">/sites/search</span>, <span class="code-inline">/documents/to-invoice</span>.</li>
        <li>Submit the following test strings in the search inputs:
            <br><span class="code-inline">' OR '1'='1</span> &nbsp;&bull;&nbsp; <span class="code-inline">test' UNION SELECT 1,2,3,4--</span> &nbsp;&bull;&nbsp; <span class="code-inline">admin'--</span>
        </li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> The application safely treats the strings as literal text; returns <em>"No records found"</em> or valid matches without triggering SQL syntax exceptions or unhandled 500 errors.
    </div>

    <div class="sub-title">Test 2.2: File & Object Storage Security (Sensitive Documents & Cloud Storage)</div>
    <ol>
        <li><strong>Direct Public Access Test:</strong> Upload a Delivery Challan or Site Drawing PDF to a project. Note its stored filename.
            <br>In an incognito browser (unauthenticated), attempt directly requesting:
            <br><span class="code-inline">http://localhost:8000/storage/projects/{id}/documents/{filename}</span>
            <br><em>Expected:</em> Confidential business files must reside in <span class="code-inline">storage/app/private</span> or private R2/S3 buckets and be accessed solely via authenticated controller streams returning 403 to unauthorized guests.
        </li>
        <li><strong>Directory Indexing Test:</strong> Navigate in browser to root asset folders:
            <br><span class="code-inline">http://localhost:8000/storage/</span>, <span class="code-inline">http://localhost:8000/storage/projects/</span>, <span class="code-inline">http://localhost:8000/build/</span>.
            <br><em>Expected:</em> Web server returns <strong>403 Forbidden</strong> or <strong>404 Not Found</strong>. Never display an index listing of directory files.
        </li>
    </ol>

    <div class="sub-title">Test 2.3: Session & Cookie Security</div>
    <ol>
        <li>Log into the CRM. Press <span class="code-inline">F12</span> -> Open <strong>Application</strong> tab -> <strong>Storage</strong> -> <strong>Cookies</strong>.</li>
        <li>Select the application domain and inspect the session cookie (<span class="code-inline">laravel_session</span> / <span class="code-inline">cctv_crm_session</span>):
            <ul>
                <li><strong>HttpOnly:</strong> Must be <strong>Checked (True)</strong>. Prevents client-side scripts from reading the session cookie.</li>
                <li><strong>SameSite:</strong> Must be set to <strong>Lax</strong> or <strong>Strict</strong>. Protects against CSRF cross-origin submissions.</li>
                <li><strong>Secure:</strong> Must be <strong>Checked (True)</strong> on production HTTPS deployments.</li>
            </ul>
        </li>
        <li><strong>Session Termination Test:</strong> Note current cookie value. Click <strong>Log Out</strong>. Verify session is regenerated/destroyed and pressing the browser Back button prevents accessing private pages.</li>
    </ol>

    <div class="sub-title">Test 2.4: Secrets & Environment Exposure (.env & Debug Mode)</div>
    <ol>
        <li>In the browser address bar, attempt requesting:
            <br><span class="code-inline">http://localhost:8000/.env</span> &nbsp;&bull;&nbsp; <span class="code-inline">http://localhost:8000/.git/config</span>
            <br><em>Expected:</em> Server configuration (Nginx / Apache) must return <strong>403 Forbidden</strong> or <strong>404 Not Found</strong>.
        </li>
        <li><strong>Production Debug Test:</strong> Deliberately request a non-existent or malformed URL.
            <br><em>Expected:</em> In production (<span class="code-inline">APP_DEBUG=false</span>), displays a clean 500 error page. It must <strong>never</strong> display Ignition debug traces with DB passwords or Razorpay secret keys.
        </li>
    </ol>

    <div class="sub-title">Test 2.5: Application Logs Inspection</div>
    <ol>
        <li>Perform standard user operations: sign in, edit user details, trigger an online payment order.</li>
        <li>Open and inspect <span class="code-inline">storage/logs/laravel.log</span>. Search for: <span class="code-inline">password</span>, <span class="code-inline">secret</span>, <span class="code-inline">razorpay_signature</span>.</li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> Authentication passwords, credit card credentials, or webhook secrets must <strong>never</strong> be written into plain text application log files.
    </div>

    <div class="page-break"></div>

    <!-- Phase 3 -->
    <div class="section-title">Phase 3: Web Application Core Protections (Manual Tests)</div>

    <div class="sub-title">Test 3.1: CSRF (Cross-Site Request Forgery) Protection</div>
    <ol>
        <li>Navigate to any authenticated form (e.g., <span class="code-inline">/leads/1/edit</span> or Project creation).</li>
        <li>Right-click the page -> <strong>Inspect Element</strong>. Locate the form.</li>
        <li>Find the hidden CSRF input: <span class="code-inline">&lt;input type="hidden" name="_token" value="..."&gt;</span>.</li>
        <li>Right-click and choose <strong>Delete Element</strong> in DevTools, then click <strong>Submit</strong>.</li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> Laravel CSRF middleware intercepts the request and throws <strong>HTTP 419 Page Expired</strong>. The unauthenticated state modification is completely rejected.
    </div>

    <div class="sub-title">Test 3.2: Stored & Reflected XSS (Cross-Site Scripting)</div>
    <ol>
        <li>Go to <strong>Leads &gt; Create Lead</strong> or <strong>Projects &gt; Create Project</strong>.</li>
        <li>Paste the following test payloads into text fields (<span class="code-inline">customer_name</span>, <span class="code-inline">title</span>, <span class="code-inline">notes</span>):
            <div class="code-box">&lt;script&gt;alert('XSS-TEST')&lt;/script&gt;
&lt;img src="invalid" onerror="alert('XSS-IMAGE')"&gt;
" autofocus onfocus="alert('XSS-FOCUS')</div>
        </li>
        <li>Save the record. Navigate to Detail view (<span class="code-inline">/projects/{id}</span>), Index table, and Customer portal view.</li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> No JavaScript popup executes. Characters are safely encoded as HTML entities (<span class="code-inline">&amp;lt;script&amp;gt;</span>) in the DOM by Blade templating engine.
    </div>

    <div class="sub-title">Test 3.3: Unrestricted File Upload Security</div>
    <ol>
        <li>Navigate to <strong>Projects &gt; Documents &gt; Upload Document</strong>.</li>
        <li>Create a test file named <span class="code-inline">test_exploit.php</span> containing:
            <div class="code-box">&lt;?php echo "Server execution vulnerability test"; ?&gt;</div>
        </li>
        <li>Attempt to upload <span class="code-inline">test_exploit.php</span>. Next, rename to <span class="code-inline">test_exploit.php.jpg</span> and attempt again.</li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> Upload rejected with validation failure: <em>"The file field must be a file of type: pdf, png, jpg, jpeg, docx"</em>. Executable scripts cannot be placed on the filesystem.
    </div>

    <div class="sub-title">Test 3.4: Payment Gateway & Webhook Signature Verification</div>
    <ol>
        <li>Test the asynchronous Razorpay Webhook endpoint (<span class="code-inline">POST /webhook/razorpay</span>).</li>
        <li>Simulate a forged external webhook payload using curl from terminal:
            <div class="code-box">curl -X POST http://localhost:8000/webhook/razorpay \
  -H "Content-Type: application/json" \
  -d '{"event":"payment.captured","payload":{"payment":{"entity":{"id":"fake_pay_123"}}}}'</div>
        </li>
    </ol>
    <div class="result-box">
        <strong>Expected Result:</strong> The controller calculates HMAC SHA-256 using the configured secret key. Failing signature verification causes an immediate <strong>HTTP 400 Bad Request</strong> or <strong>401 Unauthorized</strong>.
    </div>

    <!-- Phase 4 -->
    <div class="section-title">Phase 4: Pre-Release Manual Verification Matrix & Sign-Off</div>
    <table class="matrix">
        <thead>
            <tr>
                <th style="width: 12%;">Security Area</th>
                <th style="width: 25%;">Manual Test Step</th>
                <th style="width: 28%;">Target Route / Asset</th>
                <th style="width: 25%;">Expected Secure Outcome</th>
                <th style="width: 10%; text-align: center;">Verified</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>RBAC (Vertical)</strong></td>
                <td>Low-privilege user enters Admin URL</td>
                <td><span class="code-inline">/finance</span>, <span class="code-inline">/wage</span>, <span class="code-inline">/admin/*</span></td>
                <td>HTTP 403 Forbidden / Redirection</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>IDOR (Horizontal)</strong></td>
                <td>User A changes URL ID to User B's record</td>
                <td><span class="code-inline">/projects/documents/{id}/view</span></td>
                <td>HTTP 403 Forbidden / 404 Not Found</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>Guest Access</strong></td>
                <td>Unauthenticated user accesses private ERP</td>
                <td><span class="code-inline">/dashboard</span>, <span class="code-inline">/projects</span></td>
                <td>Redirects immediately to <span class="code-inline">/login</span></td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>CSRF Defense</strong></td>
                <td>Form submitted after deleting <span class="code-inline">_token</span></td>
                <td>Any POST/PUT/DELETE form</td>
                <td>HTTP 419 Page Expired</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>XSS Prevention</strong></td>
                <td>Input test <span class="code-inline">&lt;script&gt;alert(1)&lt;/script&gt;</span></td>
                <td>Lead / Project / Ticket text</td>
                <td>HTML-escaped in DOM; zero popups</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>Upload Validation</strong></td>
                <td>Upload <span class="code-inline">.php</span> or double extension file</td>
                <td><span class="code-inline">/projects/{project}/documents</span></td>
                <td>Validation rejection: Invalid file type</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>Storage Exposure</strong></td>
                <td>Direct browser URL to confidential PDF</td>
                <td><span class="code-inline">/storage/projects/.../documents/</span></td>
                <td>HTTP 403 / 404 (Private disk isolation)</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>Cookie Security</strong></td>
                <td>Inspect DevTools Application cookie flags</td>
                <td><span class="code-inline">laravel_session</span></td>
                <td>HttpOnly=True, SameSite=Lax/Strict</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>Secrets Isolation</strong></td>
                <td>Direct HTTP request to configuration file</td>
                <td><span class="code-inline">http://localhost:8000/.env</span></td>
                <td>HTTP 403 Forbidden / 404 Not Found</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>Log Hygiene</strong></td>
                <td>Search <span class="code-inline">storage/logs/laravel.log</span></td>
                <td><span class="code-inline">storage/logs/laravel.log</span></td>
                <td>Zero plaintext passwords or secret keys</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
            <tr>
                <td><strong>Webhook Security</strong></td>
                <td>Unsigned POST payload to payment webhook</td>
                <td><span class="code-inline">POST /webhook/razorpay</span></td>
                <td>HTTP 400 Bad Request / 401 Unauthorized</td>
                <td style="text-align: center; color: green; font-weight: bold;">[ PASS ]</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        CCTV Enterprise CRM Security Standard &bull; Precision IT Systems &bull; Generated & Verified for Enterprise Operations
    </div>

</body>
</html>
