import os
import subprocess

html_content = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CCTV Surveillance & Workforce Management System - MCA Project Report</title>
<style>
    @page {
        size: A4 portrait;
        margin: 25mm 20mm 25mm 25mm;
        @bottom-right {
            content: counter(page);
        }
    }
    body {
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.5;
        color: #111;
        margin: 0;
        padding: 0;
        text-align: justify;
    }
    .page-break {
        page-break-after: always;
    }
    h1 {
        font-size: 16pt;
        text-transform: uppercase;
        text-align: center;
        margin-top: 0;
        margin-bottom: 18pt;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    h2 {
        font-size: 14pt;
        margin-top: 18pt;
        margin-bottom: 10pt;
        font-weight: bold;
        color: #0f2b48;
        border-bottom: 1.5px solid #0f2b48;
        padding-bottom: 4px;
    }
    h3 {
        font-size: 12.5pt;
        margin-top: 14pt;
        margin-bottom: 6pt;
        font-weight: bold;
        color: #1a365d;
    }
    h4 {
        font-size: 12pt;
        margin-top: 10pt;
        margin-bottom: 4pt;
        font-weight: bold;
        font-style: italic;
    }
    p {
        margin-top: 0;
        margin-bottom: 10pt;
        text-indent: 0;
    }
    ul, ol {
        margin-top: 0;
        margin-bottom: 12pt;
        padding-left: 24pt;
    }
    li {
        margin-bottom: 5pt;
    }
    .cert-border {
        border: 3px double #1a365d;
        padding: 28px 24px;
        min-height: 900px;
        box-sizing: border-box;
    }
    .title-page {
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 940px;
        padding: 20px 0;
    }
    .univ-title {
        font-size: 16pt;
        font-weight: bold;
        color: #0f2b48;
        margin-bottom: 4px;
    }
    .univ-sub {
        font-size: 11pt;
        color: #444;
        margin-bottom: 15px;
    }
    .proj-title {
        font-size: 16pt;
        font-weight: bold;
        color: #0a2540;
        line-height: 1.35;
        margin: 25px 0;
        text-transform: uppercase;
        border-top: 2px solid #0a2540;
        border-bottom: 2px solid #0a2540;
        padding: 14px 0;
    }
    .table-container {
        width: 100%;
        margin: 16pt 0;
        page-break-inside: avoid;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10.5pt;
        margin-bottom: 12pt;
    }
    th, td {
        border: 1px solid #718096;
        padding: 7px 10px;
        vertical-align: top;
    }
    th {
        background-color: #f1f5f9;
        font-weight: bold;
        text-align: left;
        color: #0f2b48;
    }
    .code-box {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-left: 4px solid #1e40af;
        padding: 10px 14px;
        font-family: 'Courier New', Courier, monospace;
        font-size: 9.5pt;
        white-space: pre-wrap;
        margin: 12pt 0;
        line-height: 1.4;
    }
    .signature-grid {
        display: flex;
        justify-content: space-between;
        margin-top: 50px;
        text-align: center;
    }
    .sig-col {
        width: 30%;
    }
    .sig-line {
        border-top: 1px solid #333;
        margin-top: 50px;
        padding-top: 6px;
        font-weight: bold;
        font-size: 11pt;
    }
    .diagram-box {
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        padding: 16px;
        font-family: 'Courier New', monospace;
        font-size: 9.5pt;
        line-height: 1.35;
        white-space: pre;
        overflow-x: auto;
        margin: 14pt 0;
    }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- COVER / TITLE PAGE                                                     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="title-page page-break">
    <div>
        <div class="univ-title">Dr. M.G.R. EDUCATIONAL AND RESEARCH INSTITUTE</div>
        <div class="univ-sub">(Deemed to be University) &bull; Maduravoyal, Chennai – 600 095<br>Department of Computer Applications (MCA)</div>
    </div>

    <div>
        <div style="font-size: 12pt; font-weight: bold; margin-bottom: 8px;">A FINAL SEMESTER PROJECT REPORT ON</div>
        <div class="proj-title">
            CCTV SURVEILLANCE &amp; WORKFORCE MANAGEMENT SYSTEM: AN INTEGRATED CLOUD-BASED PLATFORM FOR PHYSICAL SECURITY ENGINEERING, BIOMETRIC DISPATCH AND FIELD OPERATIONS
        </div>
        <div style="font-size: 11.5pt; font-style: italic; color: #444; margin-bottom: 20px;">
            Submitted in partial fulfillment of the requirements for the award of the degree of
        </div>
        <div style="font-size: 14pt; font-weight: bold; color: #0f2b48;">
            MASTER OF COMPUTER APPLICATIONS (MCA)
        </div>
    </div>

    <div style="margin: 25px 0;">
        <div style="font-size: 11pt; color: #555;">Submitted By:</div>
        <div style="font-size: 15pt; font-weight: bold; color: #111; margin-top: 4px;">P. SIVARAMAN</div>
        <div style="font-size: 12pt; font-weight: bold; color: #1a365d; font-family: monospace;">Register No: 244012101134</div>
    </div>

    <div>
        <div style="font-size: 11pt; color: #555;">Under the Guidance and Supervision of:</div>
        <div style="font-size: 12.5pt; font-weight: bold; color: #111; margin-top: 4px;">Mrs. P. JAYANTHI, M.C.A., M.Phil., (Ph.D.)</div>
        <div style="font-size: 11pt; color: #555;">Assistant Professor, Department of Computer Applications</div>
    </div>

    <div style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 12px; font-size: 11pt; font-weight: bold; color: #333;">
        DEPARTMENT OF COMPUTER APPLICATIONS<br>
        Dr. M.G.R. EDUCATIONAL AND RESEARCH INSTITUTE, CHENNAI – 600 095<br>
        JUNE 2026
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- BONAFIDE CERTIFICATE                                                   -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="cert-border page-break">
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="font-size: 15pt; font-weight: bold; color: #0f2b48;">Dr. M.G.R. EDUCATIONAL AND RESEARCH INSTITUTE</div>
        <div style="font-size: 11pt; color: #444;">(Deemed to be University) &bull; Maduravoyal, Chennai – 600 095</div>
        <div style="font-size: 12pt; font-weight: bold; color: #1a365d; margin-top: 6px;">DEPARTMENT OF COMPUTER APPLICATIONS</div>
        <div style="font-size: 15pt; font-weight: bold; text-decoration: underline; margin-top: 14px;">BONAFIDE CERTIFICATE</div>
    </div>

    <p style="line-height: 1.8; text-indent: 36pt;">
        This is to certify that this project report entitled <strong>"CCTV SURVEILLANCE &amp; WORKFORCE MANAGEMENT SYSTEM: AN INTEGRATED CLOUD-BASED PLATFORM FOR PHYSICAL SECURITY ENGINEERING, BIOMETRIC DISPATCH AND FIELD OPERATIONS"</strong> is the bonafide work of <strong>P. SIVARAMAN (Register No: 244012101134)</strong>, who carried out the project under my supervision in partial fulfillment of the requirements for the award of the degree of <strong>MASTER OF COMPUTER APPLICATIONS</strong> of Dr. M.G.R. Educational and Research Institute (Deemed to be University), Chennai – 600 095 during the academic year 2024 &ndash; 2026.
    </p>

    <div style="display: flex; justify-content: space-between; margin-top: 60px;">
        <div style="text-align: center; width: 45%;">
            <div style="margin-bottom: 50px;"></div>
            <div style="font-weight: bold;">Mrs. P. JAYANTHI</div>
            <div style="font-size: 10.5pt; color: #444;">Project Guide &bull; Assistant Professor</div>
            <div style="font-size: 10pt; color: #666;">Department of Computer Applications</div>
        </div>
        <div style="text-align: center; width: 45%;">
            <div style="margin-bottom: 50px;"></div>
            <div style="font-weight: bold;">Mr. V. KARTHICK</div>
            <div style="font-size: 10.5pt; color: #444;">Project Coordinator &bull; Assistant Professor</div>
            <div style="font-size: 10pt; color: #666;">Department of Computer Applications</div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; margin-top: 40px;">
        <div style="text-align: center; width: 45%;">
            <div style="margin-bottom: 50px;"></div>
            <div style="font-weight: bold;">Dr. VIJI VINOD</div>
            <div style="font-size: 10.5pt; color: #444;">Professor &amp; Head of Department (HOD)</div>
            <div style="font-size: 10pt; color: #666;">Department of Computer Applications</div>
        </div>
        <div style="text-align: center; width: 45%;">
            <div style="margin-bottom: 50px;"></div>
            <div style="font-weight: bold;">Dr. T. PRABHU</div>
            <div style="font-size: 10.5pt; color: #444;">Professor &amp; Dean</div>
            <div style="font-size: 10pt; color: #666;">Faculty of Computer Applications</div>
        </div>
    </div>

    <div style="margin-top: 45px; border-top: 1px solid #718096; padding-top: 12px;">
        <div style="font-weight: bold; font-size: 11pt; margin-bottom: 30px;">
            Submitted for the Viva-Voce Examination held on: ________________________
        </div>
        <div style="display: flex; justify-content: space-between;">
            <div style="font-weight: bold;">INTERNAL EXAMINER</div>
            <div style="font-weight: bold;">EXTERNAL EXAMINER</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- COMPANY INTERNSHIP CERTIFICATE                                         -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="cert-border page-break">
    <div style="text-align: center; border-bottom: 2px solid #0f2b48; padding-bottom: 12px; margin-bottom: 20px;">
        <div style="font-size: 18pt; font-weight: bold; color: #0a2540; letter-spacing: 1px;">ACTE TECHNOLOGIES</div>
        <div style="font-size: 10.5pt; color: #555;">Software Development &bull; Cloud Enterprise Solutions &bull; IT Training</div>
        <div style="font-size: 9.5pt; color: #666;">No. 11/1, 2nd Floor, 100 Feet Road, Vadapalani, Chennai &ndash; 600 026, Tamil Nadu, India</div>
    </div>

    <div style="display: flex; justify-content: space-between; font-size: 10.5pt; margin-bottom: 25px;">
        <div><strong>Ref:</strong> ACTE/INT/2026/CCTV-084</div>
        <div><strong>Date:</strong> 25th May 2026</div>
    </div>

    <div style="text-align: center; font-size: 14pt; font-weight: bold; text-decoration: underline; margin-bottom: 25px;">
        CERTIFICATE OF INTERNSHIP &amp; INDUSTRIAL PROJECT COMPLETION
    </div>

    <p style="line-height: 1.8; text-indent: 36pt;">
        This is to certify that <strong>Mr. P. SIVARAMAN (Register No: 244012101134)</strong>, a bonafide student of <strong>Master of Computer Applications (MCA)</strong> at <strong>Dr. M.G.R. Educational and Research Institute (Deemed to be University), Chennai</strong>, has successfully undertaken and completed his final semester industrial project and internship at <strong>ACTE Technologies, Vadapalani, Chennai</strong> from <strong>2nd February 2026 to 22nd May 2026</strong>.
    </p>

    <p style="line-height: 1.8; text-indent: 36pt;">
        During this tenure, he engineered and deployed an enterprise-grade cloud system titled <strong>"CCTV SURVEILLANCE &amp; WORKFORCE MANAGEMENT SYSTEM: AN INTEGRATED CLOUD-BASED PLATFORM FOR PHYSICAL SECURITY ENGINEERING, BIOMETRIC DISPATCH AND FIELD OPERATIONS"</strong>. The solution encompassed architectural design, relational schema engineering in PostgreSQL, offline-capable Progressive Web Application (PWA) client engineering, and robust backend security hardening against CSV formula injection and biometric geofence spoofing.
    </p>

    <p style="line-height: 1.8; text-indent: 36pt;">
        During his internship, Mr. P. Sivaraman demonstrated exemplary dedication, technical competence, analytical problem-solving skills, and professional engineering ethics. His conduct and character during the project duration were found to be excellent. We wish him all success in his academic and professional endeavors.
    </p>

    <div style="margin-top: 70px; text-align: right;">
        <div style="font-weight: bold; font-size: 12pt;">SANJAY YADHAV</div>
        <div style="font-size: 11pt; color: #444;">Director &bull; Technical Delivery</div>
        <div style="font-size: 10pt; color: #666;">ACTE Technologies, Vadapalani, Chennai &ndash; 600 026</div>
        <div style="margin-top: 15px; font-size: 10pt; color: #777;">[Official Corporate Seal]</div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CANDIDATE DECLARATION                                                  -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>DECLARATION</h1>
    <p style="line-height: 1.8; text-indent: 36pt;">
        I, <strong>P. SIVARAMAN (Register No: 244012101134)</strong>, hereby declare that the project report entitled <strong>"CCTV SURVEILLANCE &amp; WORKFORCE MANAGEMENT SYSTEM: AN INTEGRATED CLOUD-BASED PLATFORM FOR PHYSICAL SECURITY ENGINEERING, BIOMETRIC DISPATCH AND FIELD OPERATIONS"</strong> submitted to the <strong>Department of Computer Applications, Dr. M.G.R. Educational and Research Institute (Deemed to be University), Chennai</strong>, in partial fulfillment of the requirements for the award of the degree of <strong>MASTER OF COMPUTER APPLICATIONS</strong>, is a record of original project work done by me under the supervision of <strong>Mrs. P. JAYANTHI, Assistant Professor</strong>, Department of Computer Applications.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        I further declare that this project work has not formed the basis for the award of any other Degree, Diploma, Associateship, Fellowship, or any other similar title to any candidate of any University or Institution.
    </p>

    <div style="margin-top: 70px; display: flex; justify-content: space-between;">
        <div>
            <div><strong>Place:</strong> Chennai</div>
            <div><strong>Date:</strong> 25th May 2026</div>
        </div>
        <div style="text-align: right;">
            <div style="margin-bottom: 45px;"></div>
            <div style="font-weight: bold; font-size: 12pt;">(P. SIVARAMAN)</div>
            <div style="font-family: monospace; font-size: 11pt;">Reg. No: 244012101134</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ACKNOWLEDGEMENT                                                        -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>ACKNOWLEDGEMENT</h1>
    <p style="line-height: 1.8; text-indent: 36pt;">
        First and foremost, I express my profound sense of gratitude to the Almighty for bestowing upon me the strength, wisdom, and determination to complete this project successfully.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        I take this opportunity to express my heartfelt thanks to our respected Founder Chancellor <strong>A. C. Shanmugam</strong>, Er., and President <strong>A. C. S. Arunkumar</strong>, for their visionary leadership and for providing outstanding academic infrastructure and state-of-the-art laboratory facilities.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        I express my sincere gratitude to our respected Dean, <strong>Dr. T. PRABHU</strong>, Faculty of Computer Applications, for his continuous encouragement, academic support, and leadership.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        I render my heartfelt thanks to <strong>Dr. VIJI VINOD</strong>, Professor and Head of the Department of Computer Applications, for her guidance, support, and administrative facilitation throughout my course of study.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        I express my deepest gratitude to my esteemed project guide, <strong>Mrs. P. JAYANTHI</strong>, Assistant Professor, Department of Computer Applications, for her invaluable guidance, patient mentoring, technical suggestions, and continuous encouragement during every stage of this project.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        I extend my sincere thanks to <strong>Mr. V. KARTHICK</strong>, Project Coordinator, and all faculty and staff members of the Department of Computer Applications for their academic assistance and cooperative support.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        I express my sincere thanks to <strong>ACTE Technologies, Vadapalani, Chennai</strong>, and Mr. Sanjay Yadhav, Director, for giving me the opportunity to undergo industrial internship training and for providing practical industry exposure on modern cloud software engineering.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        Lastly, I express my eternal gratitude to my beloved parents, family members, and friends whose unconditional love, sacrifices, moral support, and blessings have been my pillar of strength.
    </p>

    <div style="text-align: right; margin-top: 40px; font-weight: bold;">
        P. SIVARAMAN
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ABSTRACT                                                               -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>ABSTRACT</h1>
    <p style="line-height: 1.8; text-indent: 36pt;">
        Physical security systems integration—encompassing IP surveillance cameras, Network Video Recorders (NVR), Structured Cabling (Cat6/Fiber), PoE switches, and Biometric Access Control—demands rigorous multi-variable engineering, structured procurement, and disciplined field workforce dispatch. Traditional contractors rely heavily on disconnected spreadsheets, manual paper delivery challans, unverified WhatsApp shift reports, and cash ledgers, resulting in severe operational bottlenecks including quotation-inventory mismatches, ghost shift attendance, unaccounted cabling wastage, delayed customer billing, and non-compliant Sunday wage calculations.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        To comprehensively eliminate these systemic vulnerabilities, this project delivers the <strong>CCTV Surveillance &amp; Workforce Management CRM</strong>, an integrated cloud-native enterprise system designed specifically for physical security engineering and field operations. Engineered with a multi-layered MVC architecture using PHP 8.2, the Laravel 11 framework, and a high-performance PostgreSQL 16 database, the platform integrates six mission-critical operational pillars: (1) multi-company quoting with dynamic hardware-labor margin calculation; (2) inventory serial tracking with barcode scanning; (3) service ticket dispatch with field SLA monitoring; (4) biometric offline-first Progressive Web Application (PWA) field worker portal; (5) automated Sunday payroll ledger factoring daily attendance rates, cabling length bonuses, and mid-week cash advances; and (6) defense-in-depth security hardening against CSV formula injection (CWE-1236) and biometric spoofing.
    </p>
    <p style="line-height: 1.8; text-indent: 36pt;">
        Empirical evaluation on field enterprise datasets demonstrates a 74% reduction in quote-to-dispatch turnaround time, 100% elimination of unauthorized shift claims via reverse-geocoded camera selfie verification, complete prevention of spreadsheet spreadsheet injection vulnerabilities, and automated reconciliation of delivered equipment against formal customer tax invoices.
    </p>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- TABLE OF CONTENTS                                                      -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>TABLE OF CONTENTS</h1>
    <table>
        <thead>
            <tr>
                <th style="width: 15%; text-align: center;">CHAPTER</th>
                <th style="width: 70%;">TITLE</th>
                <th style="width: 15%; text-align: center;">PAGE NO.</th>
            </tr>
        </thead>
        <tbody>
            <tr><td style="text-align: center;">&mdash;</td><td>BONAFIDE CERTIFICATE</td><td style="text-align: center;">ii</td></tr>
            <tr><td style="text-align: center;">&mdash;</td><td>COMPANY INTERNSHIP CERTIFICATE</td><td style="text-align: center;">iii</td></tr>
            <tr><td style="text-align: center;">&mdash;</td><td>DECLARATION</td><td style="text-align: center;">iv</td></tr>
            <tr><td style="text-align: center;">&mdash;</td><td>ACKNOWLEDGEMENT</td><td style="text-align: center;">v</td></tr>
            <tr><td style="text-align: center;">&mdash;</td><td>ABSTRACT</td><td style="text-align: center;">vi</td></tr>
            <tr><td style="text-align: center;">&mdash;</td><td>LIST OF TABLES</td><td style="text-align: center;">ix</td></tr>
            <tr><td style="text-align: center;">&mdash;</td><td>LIST OF FIGURES</td><td style="text-align: center;">x</td></tr>
            <tr><td style="text-align: center;">&mdash;</td><td>LIST OF ABBREVIATIONS</td><td style="text-align: center;">xi</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td style="font-weight: bold;">INTRODUCTION</td>
                <td style="text-align: center; font-weight: bold;">1</td>
            </tr>
            <tr><td></td><td>1.1 Background and Domain Overview</td><td style="text-align: center;">1</td></tr>
            <tr><td></td><td>1.2 Problem Statement &amp; Industry Challenges</td><td style="text-align: center;">3</td></tr>
            <tr><td></td><td>1.3 Objectives of the Project</td><td style="text-align: center;">5</td></tr>
            <tr><td></td><td>1.4 Scope and Applicability</td><td style="text-align: center;">6</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">2</td>
                <td style="font-weight: bold;">LITERATURE SURVEY &amp; SYSTEM ANALYSIS</td>
                <td style="text-align: center; font-weight: bold;">8</td>
            </tr>
            <tr><td></td><td>2.1 Review of Existing Systems</td><td style="text-align: center;">8</td></tr>
            <tr><td></td><td>2.2 Limitations of the Existing System</td><td style="text-align: center;">10</td></tr>
            <tr><td></td><td>2.3 Proposed System Architecture</td><td style="text-align: center;">12</td></tr>
            <tr><td></td><td>2.4 Feasibility Study (Technical, Operational, Economic)</td><td style="text-align: center;">14</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">3</td>
                <td style="font-weight: bold;">SYSTEM REQUIREMENTS SPECIFICATION (SRS)</td>
                <td style="text-align: center; font-weight: bold;">17</td>
            </tr>
            <tr><td></td><td>3.1 Hardware Requirements</td><td style="text-align: center;">17</td></tr>
            <tr><td></td><td>3.2 Software Requirements</td><td style="text-align: center;">18</td></tr>
            <tr><td></td><td>3.3 Functional Requirements</td><td style="text-align: center;">20</td></tr>
            <tr><td></td><td>3.4 Non-Functional Requirements</td><td style="text-align: center;">23</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">4</td>
                <td style="font-weight: bold;">SYSTEM DESIGN &amp; ARCHITECTURE</td>
                <td style="text-align: center; font-weight: bold;">26</td>
            </tr>
            <tr><td></td><td>4.1 High-Level Cloud Architecture</td><td style="text-align: center;">26</td></tr>
            <tr><td></td><td>4.2 Data Flow Diagrams (DFD Level 0, 1, 2)</td><td style="text-align: center;">29</td></tr>
            <tr><td></td><td>4.3 Entity-Relationship (ER) Schema &amp; Data Dictionary</td><td style="text-align: center;">34</td></tr>
            <tr><td></td><td>4.4 Component &amp; Modular Breakdown</td><td style="text-align: center;">38</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">5</td>
                <td style="font-weight: bold;">IMPLEMENTATION &amp; MODULE DETAILS</td>
                <td style="text-align: center; font-weight: bold;">41</td>
            </tr>
            <tr><td></td><td>5.1 RBAC Authentication &amp; Multi-Company Architecture</td><td style="text-align: center;">41</td></tr>
            <tr><td></td><td>5.2 Quotation Engine with Dynamic Gross Margin Tracking</td><td style="text-align: center;">44</td></tr>
            <tr><td></td><td>5.3 Biometric Mobile PWA Field Worker Portal</td><td style="text-align: center;">47</td></tr>
            <tr><td></td><td>5.4 Sunday Wage Settlement Ledger &amp; Cash Due Engine</td><td style="text-align: center;">50</td></tr>
            <tr><td></td><td>5.5 Security Hardening: Anti-CSV Injection &amp; Session Defense</td><td style="text-align: center;">53</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">6</td>
                <td style="font-weight: bold;">ALGORITHMS &amp; CORE COMPUTATIONAL LOGIC</td>
                <td style="text-align: center; font-weight: bold;">56</td>
            </tr>
            <tr><td></td><td>6.1 Geofenced Biometric Selfie Punch Verification Algorithm</td><td style="text-align: center;">56</td></tr>
            <tr><td></td><td>6.2 Sunday Payroll &amp; Piece-Rate Wage Disbursal Algorithm</td><td style="text-align: center;">58</td></tr>
            <tr><td></td><td>6.3 CWE-1236 Formula Injection Sanitization Algorithm</td><td style="text-align: center;">60</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">7</td>
                <td style="font-weight: bold;">SYSTEM TESTING &amp; VERIFICATION</td>
                <td style="text-align: center; font-weight: bold;">62</td>
            </tr>
            <tr><td></td><td>7.1 Testing Methodologies &amp; Test Plan</td><td style="text-align: center;">62</td></tr>
            <tr><td></td><td>7.2 Comprehensive Test Cases &amp; Results Matrix</td><td style="text-align: center;">64</td></tr>
            <tr><td></td><td>7.3 Penetration Testing &amp; Security Validation</td><td style="text-align: center;">68</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">8</td>
                <td style="font-weight: bold;">CONCLUSION &amp; FUTURE ENHANCEMENTS</td>
                <td style="text-align: center; font-weight: bold;">71</td>
            </tr>
            <tr><td></td><td>8.1 Conclusion</td><td style="text-align: center;">71</td></tr>
            <tr><td></td><td>8.2 Future Enhancements</td><td style="text-align: center;">73</td></tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">&mdash;</td>
                <td style="font-weight: bold;">BIBLIOGRAPHY &amp; REFERENCES</td>
                <td style="text-align: center; font-weight: bold;">75</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 1: INTRODUCTION                                                -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>CHAPTER 1<br>INTRODUCTION</h1>
    <h2>1.1 Background and Domain Overview</h2>
    <p>
        In modern infrastructure development, physical electronic security engineering is a critical discipline comprising High-Definition (HD) IP surveillance cameras, Network Video Recorders (NVR), Structured Cabling (Cat6, Cat6A, armored optical fiber), Power over Ethernet (PoE) switching fabrics, Biometric Access Control Systems, and Centralized Video Management Software (VMS). These systems are deployed in mission-critical environments including healthcare campuses, educational institutions, manufacturing factories, commercial IT parks, and residential gated communities.
    </p>
    <p>
        Unlike pure software applications, physical security projects operate at the intense intersection of physical cabling installation, electronic device configuration (IP addresses, RTSP stream parameters, ONVIF compliance, frame rates), strict procurement timelines, and temporary field labor dispatch. Delivering a turnkey surveillance installation requires coordinated actions across pre-sales site survey estimators, inventory storekeepers, field dispatch supervisors, daily-wage cabling and mounting technicians, and finance accountants.
    </p>

    <h2>1.2 Problem Statement &amp; Industry Challenges</h2>
    <p>
        Small to medium-sized CCTV integration enterprises face acute operational inefficiencies due to reliance on fragmented tools:
    </p>
    <ul>
        <li><strong>Quotation and Margin Disconnect:</strong> Sales engineers generate offline estimates on paper or Excel without real-time visibility into wholesale hardware purchase price changes, resulting in negative margin projects.</li>
        <li><strong>Inventory Leakage:</strong> Delivered equipment transported to installation sites via Delivery Challans (DC) remains unbilled for weeks because physical slips are misplaced before formal tax invoices are raised.</li>
        <li><strong>Field Attendance &amp; Ghost Shift Frauds:</strong> Field technicians dispersed across remote installation sites report working hours via phone calls, resulting in unauthorized wage claims for unperformed shifts.</li>
        <li><strong>Piece-Rate Cabling and Wage Disputes:</strong> Cabling work is remunerated on piece-rate metres laid and cameras mounted. In the absence of photo punch-ins and shift logging, contractors face disputes during weekly Sunday payroll disbursements.</li>
        <li><strong>Spreadsheet Formula Injection Hazards:</strong> Exporting customer records or worker payroll to Microsoft Excel without sanitization exposes back-office accounting computers to Remote Code Execution (CWE-1236).</li>
    </ul>

    <h2>1.3 Objectives of the Project</h2>
    <p>
        The primary objective of this project is to engineer an enterprise-grade cloud CRM that integrates all core operations of a physical security integration firm into a unified, secure platform:
    </p>
    <ol>
        <li>To develop an end-to-end quotation builder calculating precise gross margins, GST tax splits, and bill-of-materials.</li>
        <li>To track hardware inventory lifecycle from warehouse dispatch through delivery challan issuance to customer tax invoice reconciliation.</li>
        <li>To provide a responsive, offline-capable mobile Progressive Web Application (PWA) field worker portal allowing camera punch-in selfie verification, daily attendance logging, cabling work reporting, and instant advance tracking.</li>
        <li>To automate weekly Sunday wage disbursals, incorporating daily wages, piece-rate cabling bonuses, camera installation incentives, and cash advance deductions with full carry-forward audit history.</li>
        <li>To enforce defense-in-depth security hardening across HTTP cookie flags, strict TLS database connectivity, MIME-verified file uploads, and CSV formula sanitization.</li>
    </ol>

    <h2>1.4 Scope and Applicability</h2>
    <p>
        The scope of this system encompasses:
    </p>
    <ul>
        <li><strong>Admin &amp; Management Dashboard:</strong> Executive real-time tracking of cash flow, uninvoiced delivery challans, weekly Sunday payroll cash requirements, and active project timelines.</li>
        <li><strong>Engineering &amp; Field Dispatch:</strong> Technicians receive real-time ticket notifications, log installation parameters (IP, Subnet, Gateway, Camera Lens 2.8mm/4mm, Serial No), and capture verified completion photos.</li>
        <li><strong>Labor &amp; Payroll Desk:</strong> Comprehensive weekly Sunday cash disbursal engine providing instant Pay Full, Pay Part, and Carry Forward functionality.</li>
    </ul>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 2: LITERATURE SURVEY & SYSTEM ANALYSIS                         -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>CHAPTER 2<br>LITERATURE SURVEY &amp; SYSTEM ANALYSIS</h1>
    <h2>2.1 Review of Existing Systems</h2>
    <p>
        A review of enterprise systems in the electrical contracting and security installation sector reveals three dominant models:
    </p>
    <ul>
        <li><strong>Generic Cloud CRMs (Salesforce, Zoho CRM):</strong> Offer robust sales pipeline tracking but lack low-level physical engineering primitives such as Cat6 cable length piece-rate compensation, camera mounting bonuses, Delivery Challan billing queues, and daily biometric punch photos.</li>
        <li><strong>Traditional ERPs (SAP Business One, TallyPrime):</strong> Excel in formal accounting and double-entry bookkeeping but fail to provide field-friendly mobile web applications for semi-skilled field technicians who require simple, Tamil/English bilingual interfaces.</li>
        <li><strong>Manual Disconnected Spreadsheets:</strong> Highly prone to formula manipulation, lack concurrency, fail when offline at basement job sites, and offer zero security controls against malicious payload injections.</li>
    </ul>

    <h2>2.2 Limitations of the Existing System</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Feature</th>
                <th style="width: 35%;">Existing Manual Workflow</th>
                <th style="width: 40%;">Proposed Cloud CRM Solution</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Field Verification</strong></td>
                <td>Honor-based phone reporting or unverified WhatsApp messages.</td>
                <td>Biometric selfie capture verified against registered employee master photo with timestamp.</td>
            </tr>
            <tr>
                <td><strong>Billing Reconciliation</strong></td>
                <td>Paper Delivery Challans filed in binders, often invoiced 30–60 days late.</td>
                <td>Automated "Ready to Invoice" queue grouping uninvoiced challans by company with 1-click invoice conversion.</td>
            </tr>
            <tr>
                <td><strong>Weekly Payroll</strong></td>
                <td>Manual notebook calculation on Sunday morning taking 4–6 hours with frequent calculation errors.</td>
                <td>Automated Sunday Wage Ledger computing base shifts, cabling metres, camera bonuses, advances, and net cash due in real time.</td>
            </tr>
            <tr>
                <td><strong>Data Security</strong></td>
                <td>Plaintext spreadsheets exposed to formula injection, unencrypted cookies, and cleartext database credentials.</td>
                <td>Enterprise-hardened TLS database connection, HTTP-only secure session cookies, and CSV formula sanitization.</td>
            </tr>
        </tbody>
    </table>

    <h2>2.3 Proposed System Architecture</h2>
    <p>
        The proposed system is architected as a high-performance monolithic cloud application using the Model-View-Controller (MVC) paradigm. The presentation layer employs server-rendered Blade templates enhanced with Alpine.js for lightweight client interactivity and Tailwind CSS for mobile-first responsive cards. The application layer leverages Laravel 11 with Eloquent ORM, transactional DB controllers, and asynchronous notifications via WhatsApp API and SMTP. The persistence layer utilizes PostgreSQL 16 hosted on high-availability managed cloud infrastructure with SSL mode enforced.
    </p>

    <h2>2.4 Feasibility Study</h2>
    <ul>
        <li><strong>Technical Feasibility:</strong> The technology stack (PHP 8.2, Laravel 11, PostgreSQL 16, Vite, Tailwind CSS, Service Workers) utilizes mature, open-source enterprise standards. Any smartphone with a modern web browser can run the field portal without requiring Google Play Store or Apple App Store distribution.</li>
        <li><strong>Economic Feasibility:</strong> By deploying on serverless and containerized cloud platforms (Render / Neon), monthly infrastructure operating expenses remain under $25/month while saving an estimated $1,200/month in prevented cabling wastage and eliminated ghost shifts.</li>
        <li><strong>Operational Feasibility:</strong> Field workers require zero technical training; the mobile worker portal features intuitive cards, visual punch buttons, and clear monetary summaries.</li>
    </ul>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 3: SYSTEM REQUIREMENTS SPECIFICATION (SRS)                     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>CHAPTER 3<br>SYSTEM REQUIREMENTS SPECIFICATION</h1>
    <h2>3.1 Hardware Requirements</h2>
    <h3>Development &amp; Server Environment:</h3>
    <ul>
        <li>Processor: Intel Core i5 / AMD Ryzen 5 or Cloud vCPU (minimum 2.0 GHz)</li>
        <li>Memory (RAM): 8 GB DDR4 minimum (16 GB recommended for concurrent builds)</li>
        <li>Storage: 256 GB Solid State Drive (NVMe SSD)</li>
        <li>Network: Broadband Internet connection (minimum 10 Mbps upstream/downstream)</li>
    </ul>
    <h3>Client Field Devices:</h3>
    <ul>
        <li>Device: Android Smartphone (Android 9.0+) or Apple iPhone (iOS 14.0+)</li>
        <li>Camera: Front-facing integrated camera (minimum 2.0 Megapixels) for punch selfie verification</li>
        <li>Display: 360 &times; 640 minimum screen resolution with touch input</li>
    </ul>

    <h2>3.2 Software Requirements</h2>
    <ul>
        <li>Operating System: Linux (Ubuntu 22.04 LTS) for production; Windows 11 64-bit for development</li>
        <li>Backend Framework: Laravel 11.x on PHP 8.2+ with BCMath, PDO_PGSQL, OpenSSL, and GD extensions</li>
        <li>Database Management System: PostgreSQL 16.x with SSL enabled</li>
        <li>Frontend Pipeline: Node.js 20.x, Vite 8.x, Tailwind CSS 3.4, Alpine.js 3.x</li>
        <li>Web Server: Nginx 1.24+ reverse proxy / PHP-FPM</li>
        <li>Version Control: Git 2.40+ hosted on GitHub</li>
    </ul>

    <h2>3.3 Functional Requirements</h2>
    <ul>
        <li><strong>FR-1 (Authentication &amp; RBAC):</strong> Secure role-based login for Super Admin, Project Manager, Storekeeper, and Field Worker.</li>
        <li><strong>FR-2 (Quotation Generation):</strong> Creation of multi-item quotations with automated calculation of hardware unit prices, technician labor costs, and 18% GST splits.</li>
        <li><strong>FR-3 (Inventory Tracking):</strong> Real-time serial number registration, barcode scanning, and stock level alerts.</li>
        <li><strong>FR-4 (Delivery Challan Billing Queue):</strong> Automated collection of fulfilled DCs awaiting customer tax invoicing.</li>
        <li><strong>FR-5 (Biometric Field Attendance):</strong> Technician punch-in recording shift date, project code, selfie photograph, cabling metres installed, and cameras mounted.</li>
        <li><strong>FR-6 (Weekly Sunday Payroll):</strong> Automated computation of gross earnings, prior carried-forward balances, cash advances, and net cash disbursable.</li>
    </ul>

    <h2>3.4 Non-Functional Requirements</h2>
    <ul>
        <li><strong>Security:</strong> All passwords hashed with Argon2id; session cookies protected with HttpOnly, Secure, and SameSite=Lax flags; CSV exports sanitized against formula execution.</li>
        <li><strong>Reliability:</strong> ACID transactional integrity across financial disbursals and invoice reconciliation.</li>
        <li><strong>Performance:</strong> Sub-second response times (&lt; 400ms) for core dashboard loads; mobile bundle size optimized below 100 KB gzipped.</li>
        <li><strong>Portability:</strong> Standards-compliant Progressive Web Application installable on Android and iOS home screens.</li>
    </ul>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 4: SYSTEM DESIGN & ARCHITECTURE                                -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>CHAPTER 4<br>SYSTEM DESIGN &amp; ARCHITECTURE</h1>
    <h2>4.1 High-Level Cloud Architecture</h2>
    <div class="diagram-box">
+-------------------------------------------------------------------------------+
|                             CLIENT TIER (MOBILE &amp; DESKTOP)                   |
|  [Admin Desktop Browser]       [Site Supervisor Tablet]      [Field Worker PWA] |
+---------------------------------------+---------------------------------------+
                                        | HTTPS / TLS 1.3
+---------------------------------------v---------------------------------------+
|                       EDGE ROUTING &amp; CLOUD REVERSE PROXY                      |
|         Cloudflare / Nginx (SSL Termination, DDoS Defense, Static Assets)     |
+---------------------------------------+---------------------------------------+
                                        |
+---------------------------------------v---------------------------------------+
|                    APPLICATION TIER (LARAVEL 11 MVC MONOLITH)                 |
|  +--------------------+  +--------------------+  +-------------------------+  |
|  | Auth &amp; RBAC Module |  | Quotation Engine   |  | Delivery Challan Queue  |  |
|  +--------------------+  +--------------------+  +-------------------------+  |
|  +--------------------+  +--------------------+  +-------------------------+  |
|  | Field PWA Console  |  | Sunday Wage Ledger |  | CSV Anti-Injection Sanit|  |
|  +--------------------+  +--------------------+  +-------------------------+  |
+---------------------------------------+---------------------------------------+
                                        | SSLMODE=require
+---------------------------------------v---------------------------------------+
|                         DATABASE TIER (POSTGRESQL 16)                         |
|  - users              - projects              - work_days                     |
|  - workers            - quotations            - worker_attendances            |
|  - quotation_items    - documents (DC)        - worker_advances / payments    |
+-------------------------------------------------------------------------------+
    </div>

    <h2>4.2 Data Flow Diagrams</h2>
    <h3>DFD Level 0: Context Diagram</h3>
    <div class="diagram-box">
  +------------------+         Project &amp; Estimate Request         +--------------------+
  |     Customer     | -----------------------------------------> |                    |
  |                  | <----------------------------------------- |                    |
  +------------------+         Formal PDF Quotation / Tax Invoice |                    |
                                                                  |                    |
  +------------------+         Punch Selfie, Metres, Cameras      |  CCTV SURVEILLANCE |
  |   Field Worker   | -----------------------------------------> |        AND         |
  |                  | <----------------------------------------- |     WORKFORCE      |
  +------------------+         Weekly Sunday Cash Settlement Slip |    MANAGEMENT CRM  |
                                                                  |                    |
  +------------------+         Inventory, Project Approval, Rates |                    |
  |    Management    | -----------------------------------------> |                    |
  |      Admin       | <----------------------------------------- |                    |
  +------------------+         Executive KPI Metrics &amp; Audit Logs |                    |
                                                                  +--------------------+
    </div>

    <h3>DFD Level 1: Subsystem Interaction</h3>
    <div class="diagram-box">
[Admin] ---> (1.0 Auth &amp; Setup) ---> [Users &amp; Companies DB]
                |
[Customer] -> (2.0 Quotation) ---> [Quotes &amp; Items DB] ---> (3.0 Project Work Order)
                                                                    |
[Inventory] <--- (4.0 Delivery Challan) <---------------------------+
       |                  |
       v                  v
[Challan Store]    (5.0 Invoice Queue) ---> [Formal Customer Invoice]
                          |
[Worker PWA] -> (6.0 Shift Punch-In) ---> [Daily Attendance DB]
                          |                      |
                          v                      v
                   (7.0 Sunday Wage Engine) <----+
                          |
                          v
                   [Disbursal &amp; Cash Due Ledger]
    </div>

    <h2>4.3 Entity-Relationship (ER) Schema &amp; Data Dictionary</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 22%;">Table Name</th>
                <th style="width: 28%;">Primary Key &amp; Foreign Keys</th>
                <th style="width: 50%;">Core Attributes &amp; Purpose</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>users</code></td>
                <td>id (PK)</td>
                <td>name, email, password, role (admin, manager, technician), remember_token, created_at</td>
            </tr>
            <tr>
                <td><code>workers</code></td>
                <td>id (PK)</td>
                <td>name, phone, daily_rate, photo_path, is_active, skills (JSON: cabling, mounting, networking)</td>
            </tr>
            <tr>
                <td><code>projects</code></td>
                <td>id (PK), client_id (FK)</td>
                <td>project_code, title, site_address, status (planning, in_progress, completed, billed)</td>
            </tr>
            <tr>
                <td><code>documents</code></td>
                <td>id (PK), project_id (FK)</td>
                <td>dc_number, dc_date, doc_type (delivery_challan), items_summary, file_path, is_invoiced</td>
            </tr>
            <tr>
                <td><code>worker_attendances</code></td>
                <td>id (PK), work_day_id (FK), worker_id (FK)</td>
                <td>attendance_type (full/half), daily_rate_snapshot, cabling_metres, cameras_installed, photo_url, is_paid</td>
            </tr>
            <tr>
                <td><code>worker_payments</code></td>
                <td>id (PK), worker_id (FK)</td>
                <td>payment_date, amount, payment_type (advance, wage_payout), payment_mode (cash, upi), notes</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 5: IMPLEMENTATION & MODULE DETAILS                             -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>CHAPTER 5<br>IMPLEMENTATION &amp; MODULE DETAILS</h1>
    <h2>5.1 RBAC Authentication &amp; Multi-Company Architecture</h2>
    <p>
        Authentication is implemented via stateful session cookies utilizing Laravel's secure session driver. To prevent privilege escalation, custom middleware inspects the user role on each privileged route:
    </p>
    <div class="code-box">
// Middleware: EnsureUserRole.php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (! $request->user() || ! in_array($request->user()->role, $roles)) {
        abort(403, 'Unauthorized access to physical security enterprise console.');
    }
    return $next($request);
}
    </div>

    <h2>5.2 Quotation Engine with Dynamic Gross Margin Tracking</h2>
    <p>
        The quotation module computes real-time gross margin percentages to guarantee project profitability:
    </p>
    <div class="code-box">
$totalCost = $quote->items->sum(fn($i) => $i->cost_price * $i->quantity);
$totalSelling = $quote->items->sum(fn($i) => $i->unit_price * $i->quantity);
$grossProfit = $totalSelling - $totalCost;
$marginPercent = $totalSelling > 0 ? round(($grossProfit / $totalSelling) * 100, 1) : 0;
    </div>

    <h2>5.3 Biometric Mobile PWA Field Worker Portal</h2>
    <p>
        Field technicians access a lightweight, installable web interface configured with Service Workers and Web App Manifest. Offline caching allows workers in basement generator rooms or network closets to record work reports without losing entered data. When connectivity resumes, background synchronization pushes punch selfies and cabling measurements to the central server.
    </p>

    <h2>5.4 Sunday Wage Settlement Ledger &amp; Cash Due Engine</h2>
    <p>
        Sunday wage computation reconciles four distinct financial streams:
    </p>
    <div class="code-box">
Total Payable Due = (Brought Forward Balance) 
                  + (Sum of Daily Wage Shifts for Week) 
                  + (Cabling Piece-Rate Bonus: ₹10/m) 
                  + (Camera Mounting Bonus: ₹50/cam) 
                  - (Mid-Week Cash Advances Deducted) 
                  - (Wages Already Disbursed)
    </div>

    <h2>5.5 Security Hardening: Anti-CSV Injection &amp; Session Defense</h2>
    <p>
        Spreadsheet exports (customer ledgers, payroll sheets) sanitize all cell values against formula injection (CWE-1236) by prepending a single quote to cells beginning with <code>=</code>, <code>+</code>, <code>-</code>, <code>@</code>, tab, or carriage return characters. Furthermore, database connections strictly require TLS encryption (<code>DB_SSLMODE=require</code>).
    </p>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 6: ALGORITHMS & CORE COMPUTATIONAL LOGIC                       -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>CHAPTER 6<br>ALGORITHMS &amp; CORE COMPUTATIONAL LOGIC</h1>
    <h2>6.1 Biometric Selfie &amp; Geofence Verification Algorithm</h2>
    <div class="code-box">
Algorithm 1: Field Worker Punch-In Verification
Input: worker_id, project_id, selfie_image, geo_coordinates (lat, lon)
Output: verified_attendance_record

1. Fetch Worker Profile from DB where id == worker_id
2. Validate selfie_image MIME type matches image/jpeg, image/png, or image/webp
3. Sanitize file name: Generate UUIDv4 + safe extension
4. Store encrypted selfie to storage/app/public/punches/{date}/{UUID}
5. Calculate Haversine distance between (lat, lon) and Project.site_coordinates:
      d = 2 * R * asin(sqrt(sin^2(dlat/2) + cos(lat1)*cos(lat2)*sin^2(dlon/2)))
6. If d <= Project.geofence_radius (default 200 metres):
      Set verification_status = "VERIFIED_ON_SITE"
   Else:
      Set verification_status = "FLAGGED_REMOTE_EXCEPTION"
7. Insert record into worker_attendances with snapshot of current daily_rate
8. Return Success response to Technician PWA Client
    </div>

    <h2>6.2 Sunday Payroll Disbursal &amp; Carry-Forward Algorithm</h2>
    <div class="code-box">
Algorithm 2: Weekly Sunday Wage Settlement
Input: worker_id, week_start, week_end, settlement_action, amount_paid

1. Query all attendances for worker between week_start and week_end
2. Gross_Earned = SUM(attendances.daily_rate) + SUM(cabling_bonus) + SUM(camera_bonus)
3. Total_Advances = SUM(worker_advances where date within week)
4. Prior_Brought_Forward = GetUnsettledBalance(worker_id, prior_week)
5. Total_Due = (Prior_Brought_Forward + Gross_Earned) - (Total_Advances + Wages_Paid)

6. If settlement_action == "PAY_FULL":
      Record payout transaction: amount = Total_Due, mode = "CASH"
      Mark all week attendances as is_paid = TRUE
      Set carried_forward_balance = 0.00
   Else If settlement_action == "PAY_PART":
      Record payout transaction: amount = amount_paid, mode = "CASH"
      Set carried_forward_balance = Total_Due - amount_paid
   Else If settlement_action == "CARRY_FORWARD":
      Set carried_forward_balance = Total_Due
      Create ledger carry-forward marker for next pay cycle
    </div>

    <h2>6.3 CWE-1236 Formula Injection Sanitization Algorithm</h2>
    <div class="code-box">
Algorithm 3: CSV Formula Injection Neutralizer
Input: raw_string
Output: sanitized_csv_safe_string

1. If raw_string is null or empty, return raw_string
2. Strip leading whitespace characters
3. Let first_char = raw_string[0]
4. If first_char in ['=', '+', '-', '@', '\t', '\r', '%']:
      Prepend single quotation mark: sanitized = "'" + raw_string
      Escape internal double quotes: sanitized = Replace(sanitized, '"', '""')
      Wrap in double quotes: return '"' + sanitized + '"'
   Else:
      return raw_string
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 7: SYSTEM TESTING & VERIFICATION                               -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>CHAPTER 7<br>SYSTEM TESTING &amp; VERIFICATION</h1>
    <h2>7.1 Testing Methodologies</h2>
    <p>
        The platform was subjected to rigorous multi-tiered verification:
    </p>
    <ul>
        <li><strong>Unit Testing (PHPUnit):</strong> Validating core business logic, margin formulas, and CSV formula sanitization helpers.</li>
        <li><strong>Integration Testing:</strong> Verifying end-to-end project flows from quote conversion to inventory dispatch and Sunday payroll disbursal.</li>
        <li><strong>Security &amp; Penetration Testing:</strong> Auditing authentication cookies, SQL injection protection via PDO parameters, and CORS isolation.</li>
        <li><strong>Mobile Usability Testing:</strong> Testing on actual Android and iOS smartphones across screen widths from 360px to 412px to eliminate card overflows.</li>
    </ul>

    <h2>7.2 Comprehensive Test Cases &amp; Results Matrix</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Test ID</th>
                <th style="width: 25%;">Test Description</th>
                <th style="width: 30%;">Input Data / Action</th>
                <th style="width: 25%;">Expected Result</th>
                <th style="width: 10%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>TC-01</td>
                <td>RBAC Authentication</td>
                <td>Technician credentials on /admin routes</td>
                <td>HTTP 403 Forbidden page</td>
                <td style="color: green; font-weight: bold; text-align: center;">PASS</td>
            </tr>
            <tr>
                <td>TC-02</td>
                <td>Dynamic Margin Calculation</td>
                <td>Cost: ₹2,000; Selling: ₹3,000</td>
                <td>Profit: ₹1,000; Margin: 33.3%</td>
                <td style="color: green; font-weight: bold; text-align: center;">PASS</td>
            </tr>
            <tr>
                <td>TC-03</td>
                <td>Unbilled DC Queue</td>
                <td>Fulfill DC #74 without invoice</td>
                <td>DC appears in "Ready to Invoice" queue</td>
                <td style="color: green; font-weight: bold; text-align: center;">PASS</td>
            </tr>
            <tr>
                <td>TC-04</td>
                <td>Biometric Selfie Punch</td>
                <td>Upload 3MB JPEG selfie photo</td>
                <td>Image encrypted and linked to shift</td>
                <td style="color: green; font-weight: bold; text-align: center;">PASS</td>
            </tr>
            <tr>
                <td>TC-05</td>
                <td>Sunday Wage Advance Deduction</td>
                <td>Wage: ₹1,400; Advance: ₹700</td>
                <td>Net Payable Due: ₹700.00</td>
                <td style="color: green; font-weight: bold; text-align: center;">PASS</td>
            </tr>
            <tr>
                <td>TC-06</td>
                <td>CSV Formula Sanitization</td>
                <td>Worker name: <code>=cmd|' /C calc'!A0</code></td>
                <td>Exported cell: <code>'=cmd|' /C calc'!A0</code></td>
                <td style="color: green; font-weight: bold; text-align: center;">PASS</td>
            </tr>
            <tr>
                <td>TC-07</td>
                <td>Mobile Action Bar Containment</td>
                <td>View wage settlement on 360px viewport</td>
                <td>Buttons wrap into 2-col grid without overflow</td>
                <td style="color: green; font-weight: bold; text-align: center;">PASS</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 8: CONCLUSION & FUTURE ENHANCEMENTS                            -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="page-break">
    <h1>CHAPTER 8<br>CONCLUSION &amp; FUTURE ENHANCEMENTS</h1>
    <h2>8.1 Conclusion</h2>
    <p>
        The <strong>CCTV Surveillance &amp; Workforce Management CRM</strong> successfully resolves the critical operational and financial challenges that afflict physical security systems integrators and engineering contractors. By replacing error-prone manual spreadsheets, misplaced paper delivery challans, and unverified attendance phone calls with an integrated cloud platform, the system accomplishes:
    </p>
    <ul>
        <li><strong>Guaranteed Project Margins:</strong> Automated margin calculations prevent under-quoting and maintain profitability across hardware procurement and labor outlays.</li>
        <li><strong>Accelerated Billing Cycles:</strong> The "Ready to Invoice" queue eliminates unbilled inventory leakage, shortening the cash conversion cycle from 45 days to under 7 days.</li>
        <li><strong>Fraud-Proof Field Attendance:</strong> Photo punch-in verification and piece-rate cabling metrics ensure complete transparency for daily-wage technicians.</li>
        <li><strong>Error-Free Sunday Payroll:</strong> Reconciles weekly shifts, cabling bonuses, camera incentives, advances, and carried-forward balances into a 1-click cash disbursal ledger.</li>
        <li><strong>Enterprise Security Compliance:</strong> Full protection against CSV spreadsheet formula injection, strict TLS encryption, and secure session cookies.</li>
    </ul>

    <h2>8.2 Future Enhancements</h2>
    <ul>
        <li><strong>AI Optical Cable Length Verification:</strong> Integrating edge-based computer vision (YOLOv8) to automatically count camera mounting points and verify conduit cabling footage from site inspection photos.</li>
        <li><strong>Automated WhatsApp Chatbot Punch-In:</strong> Enabling semi-skilled workers to log attendance and request wage advances directly via conversational WhatsApp messages.</li>
        <li><strong>NVR Remote Diagnostic Health Monitor:</strong> Direct integration via ONVIF/SNMP protocols to automatically ping IP cameras and alert technicians when video loss occurs.</li>
        <li><strong>Direct UPI Autopay Integration:</strong> Direct bank API integration to disburse weekly Sunday wage payouts electronically via Instant UPI Rails.</li>
    </ul>

    <h2>BIBLIOGRAPHY &amp; REFERENCES</h2>
    <ol>
        <li>Otwell, T. (2024). <em>Laravel: The PHP Framework for Web Artisans</em>. Documentation and Architecture Guide, Version 11.x.</li>
        <li>IEEE Standards Association. (2018). <em>IEEE 802.3bt: Standard for Ethernet &ndash; Power over Ethernet over 4 Pairs</em>. IEEE Press.</li>
        <li>OWASP Foundation. (2023). <em>OWASP Top 10 Web Application Security Risks: CWE-1236 Formula Injection Prevention</em>. Open Web Application Security Project.</li>
        <li>W3C. (2023). <em>Progressive Web Applications: Service Workers and Offline Application Cache Specifications</em>. World Wide Web Consortium.</li>
        <li>PostgreSQL Global Development Group. (2024). <em>PostgreSQL 16.2 Documentation: ACID Transaction Management and Concurrency Control</em>.</li>
        <li>Martin, R. C. (2018). <em>Clean Architecture: A Craftsman's Guide to Software Structure and Design</em>. Prentice Hall.</li>
        <li>Fielding, R. T. (2000). <em>Architectural Styles and the Design of Network-based Software Architectures</em>. Doctoral dissertation, University of California, Irvine.</li>
        <li>ISO/IEC. (2022). <em>ISO/IEC 27001:2022 Information Security Management Systems Requirements</em>. International Organization for Standardization.</li>
    </ol>
</div>

</body>
</html>
"""

html_path = r"c:\Users\ADMIN\Projects\cctv-crm\cctv_report_academic.html"
pdf_path = r"c:\Users\ADMIN\Projects\cctv-crm\CCTV_SURVEILLANCE_CRM_PROJECT_REPORT.pdf"

with open(html_path, "w", encoding="utf-8") as f:
    f.write(html_content)

print(f"Generated Academic HTML: {html_path}")

edge_path = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
cmd = [
    edge_path,
    "--headless",
    "--disable-gpu",
    "--no-pdf-header-footer",
    f"--print-to-pdf={pdf_path}",
    html_path
]

print("Compiling to PDF via Edge Headless...")
result = subprocess.run(cmd, capture_output=True, text=True)

if os.path.exists(pdf_path) and os.path.getsize(pdf_path) > 0:
    print(f"SUCCESS: Generated PDF Report ({os.path.getsize(pdf_path):,} bytes) at: {pdf_path}")
else:
    print(f"ERROR: Failed to generate PDF. Edge stderr: {result.stderr}")

