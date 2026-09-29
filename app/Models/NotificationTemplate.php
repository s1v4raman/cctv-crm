<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $table = 'notification_templates';

    protected $fillable = [
        'event_key',
        'title',
        'category',
        'whatsapp_template',
        'sms_template',
        'email_subject',
        'email_body',
        'available_variables',
        'is_whatsapp_enabled',
        'is_sms_enabled',
        'is_email_enabled',
    ];

    protected $attributes = [
        'is_whatsapp_enabled' => true,
        'is_sms_enabled'      => true,
        'is_email_enabled'    => true,
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_whatsapp_enabled' => 'boolean',
        'is_sms_enabled'      => 'boolean',
        'is_email_enabled'    => 'boolean',
    ];

    public static function defaultTemplates(): array
    {
        return [
            // 1. Quotations Module
            'quotation_sent' => [
                'title'               => 'Quotation Dispatch & Proposal',
                'category'            => 'quotations',
                'whatsapp_template'   => "Hello {customer_name},\n\nThank you for reaching out to us. We have prepared your CCTV Security Quotation *{quote_no}* for total amount *{total_amount}*.\n\n📄 View Quotation PDF: {link}\n\nThis quote is valid until {valid_until}. Please let us know if you have any questions!",
                'sms_template'        => "Dear {customer_name}, your CCTV Quotation {quote_no} for {total_amount} is ready. View here: {link}",
                'email_subject'       => "Your CCTV Security Quotation ({quote_no})",
                'email_body'          => "Dear {customer_name},<br><br>Thank you for considering us for your security surveillance needs. Please find your detailed CCTV Quotation <strong>{quote_no}</strong> for <strong>{total_amount}</strong>.<br><br><a href='{link}'>Click here to view your Quotation PDF</a>.<br><br>Valid until: {valid_until}.",
                'available_variables' => ['customer_name', 'quote_no', 'total_amount', 'valid_until', 'link'],
            ],
            'quotation_expiring_soon' => [
                'title'               => 'Quotation Expiry Reminder (3 Days Notice)',
                'category'            => 'quotations',
                'whatsapp_template'   => "Hello {customer_name},\n\nThis is a friendly reminder that your CCTV Security Quotation *{quote_no}* (Amount: *{total_amount}*) is scheduled to expire in 3 days on *{valid_until}*.\n\n📄 Review Proposal Online: {link}\n\nTo lock in your pricing and schedule fast installation, please confirm your acceptance before expiration.",
                'sms_template'        => "Reminder: CCTV Quotation {quote_no} for {total_amount} expires on {valid_until}. Review here: {link}",
                'email_subject'       => "Reminder: Your CCTV Quotation ({quote_no}) Expires in 3 Days",
                'email_body'          => "Dear {customer_name},<br><br>This is a reminder that your CCTV proposal <strong>{quote_no}</strong> for <strong>{total_amount}</strong> is expiring on <strong>{valid_until}</strong>.<br><br><a href='{link}'>Click here to review & approve your quotation</a> before pricing expires.",
                'available_variables' => ['customer_name', 'quote_no', 'total_amount', 'valid_until', 'link'],
            ],

            // 2. Leads Module
            'lead_created' => [
                'title'               => 'New Lead & Inquiry Registration',
                'category'            => 'leads',
                'whatsapp_template'   => "Hello {customer_name},\n\nThank you for reaching out to us! We have received your inquiry for CCTV Surveillance & Security Solutions.\n\n📍 Site Location: {site_address}\n🏢 Company: {company_name}\n\nOur solutions consultant will contact you at {phone} shortly to schedule a site survey or discuss custom surveillance options.",
                'sms_template'        => "Dear {customer_name}, thank you for contacting us regarding CCTV solutions. Our team will contact you shortly.",
                'email_subject'       => "Thank You for Contacting Us Regarding CCTV Security Solutions",
                'email_body'          => "Dear {customer_name},<br><br>Thank you for getting in touch with us. We have received your inquiry for security surveillance systems at <strong>{site_address}</strong>.<br><br>Our expert consultant will review your site specifications and contact you shortly.",
                'available_variables' => ['customer_name', 'phone', 'email', 'site_address', 'company_name', 'lead_source'],
            ],
            'lead_status_updated' => [
                'title'               => 'Lead Status Transition Update',
                'category'            => 'leads',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour CCTV project consultation status for *{site_address}* has been updated to: *{status}*.\n\nNotes: {notes}\n\nThank you for choosing us!",
                'sms_template'        => "Dear {customer_name}, your CCTV project consultation status is now {status}.",
                'email_subject'       => "Lead Status Update: {status}",
                'email_body'          => "Dear {customer_name},<br><br>Your CCTV consultation status for <strong>{site_address}</strong> has been updated to <strong>{status}</strong>.<br><br>Notes: {notes}",
                'available_variables' => ['customer_name', 'status', 'site_address', 'company_name', 'notes'],
            ],

            // 3. Site Surveys Module
            'survey_scheduled' => [
                'title'               => 'Site Survey Scheduled',
                'category'            => 'surveys',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour on-site CCTV assessment & survey has been scheduled for *{survey_date}*.\n\n📍 Site Address: {site_address}\n👷 Surveyor / Engineer: *{technician_name}* ({technician_phone})\n\nPlease ensure site access is available.",
                'sms_template'        => "CCTV site survey scheduled on {survey_date} at {site_address}. Engineer: {technician_name} ({technician_phone}).",
                'email_subject'       => "CCTV Site Survey Scheduled for {survey_date}",
                'email_body'          => "Dear {customer_name},<br><br>We have scheduled a comprehensive site survey for your premises at <strong>{site_address}</strong> on <strong>{survey_date}</strong>.<br><br>Assigned Surveyor: <strong>{technician_name}</strong> (Phone: {technician_phone}).",
                'available_variables' => ['customer_name', 'survey_date', 'site_address', 'technician_name', 'technician_phone', 'contact_person', 'notes'],
            ],
            'survey_completed' => [
                'title'               => 'Site Survey Completed & Report Ready',
                'category'            => 'surveys',
                'whatsapp_template'   => "Hello {customer_name},\n\nOur engineer *{technician_name}* has completed the CCTV site survey for *{site_address}*.\n\nWe are now preparing your customized camera layout proposal and quotation!",
                'sms_template'        => "Dear {customer_name}, CCTV site survey for {site_address} is completed. Quotation in progress.",
                'email_subject'       => "CCTV Site Survey Completed - Proposal Preparation",
                'email_body'          => "Dear {customer_name},<br><br>The site assessment for <strong>{site_address}</strong> has been completed by <strong>{technician_name}</strong>.<br><br>Our team is preparing your custom quotation and camera layout diagram.",
                'available_variables' => ['customer_name', 'survey_date', 'site_address', 'technician_name', 'notes'],
            ],

            // 4. Installation Jobs Module
            'job_scheduled' => [
                'title'               => 'Installation Job Scheduled & Assigned',
                'category'            => 'jobs',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour CCTV installation job *{job_no}* has been scheduled for *{scheduled_date}*.\n\nAttending Lead Technician: *{technician_name}* ({technician_phone}).\n\nOur team will arrive on time with the equipment.",
                'sms_template'        => "Dear {customer_name}, CCTV installation job {job_no} is scheduled for {scheduled_date}. Tech: {technician_name} ({technician_phone}).",
                'email_subject'       => "CCTV Installation Scheduled ({job_no})",
                'email_body'          => "Dear {customer_name},<br><br>We are pleased to inform you that your CCTV installation job <strong>{job_no}</strong> is scheduled for <strong>{scheduled_date}</strong>.<br><br>Assigned Lead Technician: <strong>{technician_name}</strong> (Phone: {technician_phone}).",
                'available_variables' => ['customer_name', 'job_no', 'scheduled_date', 'technician_name', 'technician_phone', 'site_address'],
            ],
            'job_completed' => [
                'title'               => 'Installation Job Completed & Handover Signed',
                'category'            => 'jobs',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour CCTV installation job *{job_no}* has been successfully completed and tested!\n\n📋 Handover Report: *{report_no}*\nLead Technician: *{technician_name}*\n📄 View Signed Completion Report: {jcr_link}\n\nThank you for choosing us for your security surveillance!",
                'sms_template'        => "CCTV installation {job_no} completed. Handover report {report_no}: {jcr_link}",
                'email_subject'       => "CCTV Installation Completed & Handover Certificate ({job_no})",
                'email_body'          => "Dear {customer_name},<br><br>We are pleased to inform you that your CCTV installation job <strong>{job_no}</strong> has been completed and verified.<br><br>Signed Handover Report: <strong>{report_no}</strong><br><br><a href='{jcr_link}'>View Handover Report & Certificate</a>.",
                'available_variables' => ['customer_name', 'job_no', 'technician_name', 'report_no', 'jcr_link', 'completed_date'],
            ],

            // 5. AMC Maintenance Contracts Module
            'amc_visit_reminder' => [
                'title'               => 'AMC Routine Maintenance Visit (48h Reminder)',
                'category'            => 'amc',
                'whatsapp_template'   => "Hello {customer_name},\n\nThis is a friendly reminder that your periodic CCTV maintenance servicing visit under AMC *{contract_no}* is scheduled for *{visit_date}*.\n\nAttending Technician: *{technician_name}*.\n\nPlease ensure the DVR/NVR room is accessible.",
                'sms_template'        => "Reminder: CCTV AMC maintenance visit for {contract_no} is scheduled for {visit_date}. Tech: {technician_name}.",
                'email_subject'       => "Upcoming CCTV AMC Maintenance Visit ({contract_no})",
                'email_body'          => "Dear {customer_name},<br><br>Your scheduled quarterly/periodic CCTV maintenance visit under AMC Contract <strong>{contract_no}</strong> will take place on <strong>{visit_date}</strong> with technician <strong>{technician_name}</strong>.",
                'available_variables' => ['customer_name', 'contract_no', 'visit_date', 'technician_name'],
            ],
            'amc_expiry_alert' => [
                'title'               => 'AMC Contract Expiry & Renewal Notice',
                'category'            => 'amc',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour CCTV Annual Maintenance Contract *{contract_no}* is expiring on *{expiry_date}*.\n\nProtect your CCTV surveillance and continuous priority support with uninterrupted coverage.\n\n🔄 Request One-Click Renewal: {renewal_link}",
                'sms_template'        => "Dear {customer_name}, your CCTV AMC {contract_no} expires on {expiry_date}. Renew here: {renewal_link}",
                'email_subject'       => "Urgent: Your CCTV AMC Contract ({contract_no}) is Expiring Soon",
                'email_body'          => "Dear {customer_name},<br><br>Your Annual Maintenance Contract (AMC) <strong>{contract_no}</strong> is set to expire on <strong>{expiry_date}</strong>.<br><br><a href='{renewal_link}'>Click here to renew your AMC contract online</a>.",
                'available_variables' => ['customer_name', 'contract_no', 'expiry_date', 'renewal_link'],
            ],

            // 6. Service Tickets Module
            'ticket_created' => [
                'title'               => 'Service Ticket Registered',
                'category'            => 'service',
                'whatsapp_template'   => "Hello {customer_name},\n\nWe have logged your CCTV support ticket *{ticket_no}* for issue: *{title}* (Priority: {priority}).\n\nOur service engineer is reviewing your request and will contact you shortly.",
                'sms_template'        => "CCTV Support: Ticket {ticket_no} ({title}) has been logged. Priority: {priority}.",
                'email_subject'       => "Support Ticket Registered ({ticket_no})",
                'email_body'          => "Dear {customer_name},<br><br>Your service ticket <strong>{ticket_no}</strong> for <em>{title}</em> has been received and assigned priority <strong>{priority}</strong>.",
                'available_variables' => ['customer_name', 'ticket_no', 'title', 'priority'],
            ],
            'ticket_status_updated' => [
                'title'               => 'Service Ticket Status Update',
                'category'            => 'service',
                'whatsapp_template'   => "Hello {customer_name},\n\nUpdate on CCTV Ticket *{ticket_no}*:\nStatus: *{status}*\nEngineer: *{technician_name}*\nNotes: {notes}\n\nTrack progress: {ticket_link}",
                'sms_template'        => "CCTV Ticket {ticket_no} status updated to {status}. Assigned: {technician_name}.",
                'email_subject'       => "Ticket #{ticket_no} Status Update: {status}",
                'email_body'          => "Dear {customer_name},<br><br>Your service ticket <strong>{ticket_no}</strong> status is now <strong>{status}</strong>.<br><br>Assigned Engineer: {technician_name}<br>Notes: {notes}<br><br><a href='{ticket_link}'>View Ticket Details</a>.",
                'available_variables' => ['customer_name', 'ticket_no', 'status', 'technician_name', 'notes', 'ticket_link'],
            ],
            'ticket_resolved' => [
                'title'               => 'Service Ticket Resolved & Signed',
                'category'            => 'service',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour CCTV service ticket *{ticket_no}* has been marked as resolved.\n\nResolution Notes: {resolution_notes}\n\n📄 View Handover Certificate: {jcr_link}\n\nThank you for choosing us!",
                'sms_template'        => "CCTV Support: Ticket {ticket_no} is resolved. View resolution: {jcr_link}",
                'email_subject'       => "Service Ticket Resolved ({ticket_no})",
                'email_body'          => "Dear {customer_name},<br><br>Your CCTV service ticket <strong>{ticket_no}</strong> has been resolved.<br><br>Resolution: {resolution_notes}<br><br><a href='{jcr_link}'>View Signed Completion Certificate</a>.",
                'available_variables' => ['customer_name', 'ticket_no', 'resolution_notes', 'jcr_link'],
            ],

            // 7. Invoices & Billing Module
            'invoice_generated' => [
                'title'               => 'Tax Invoice Issued',
                'category'            => 'invoices',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour Tax Invoice *{invoice_no}* for total amount *{grand_total}* has been generated.\n\nDue Date: {due_date}\n📄 Download Invoice PDF: {link}\n\nThank you for your business!",
                'sms_template'        => "Tax Invoice {invoice_no} for {grand_total} issued. Due date: {due_date}. View: {link}",
                'email_subject'       => "Tax Invoice Issued ({invoice_no})",
                'email_body'          => "Dear {customer_name},<br><br>Please find your tax invoice <strong>{invoice_no}</strong> for <strong>{grand_total}</strong>.<br><br>Payment Due Date: {due_date}.<br><br><a href='{link}'>Download Tax Invoice PDF</a>.",
                'available_variables' => ['customer_name', 'invoice_no', 'grand_total', 'due_date', 'link'],
            ],
            'payment_receipt' => [
                'title'               => 'Payment Receipt & Acknowledgement',
                'category'            => 'invoices',
                'whatsapp_template'   => "Hello {customer_name},\n\nWe have received your payment of *{amount_paid}* via *{payment_method}* for Invoice *{invoice_no}*.\n\nRemaining Balance: *{balance_due}*.\n\nThank you for your prompt payment!",
                'sms_template'        => "Payment received: {amount_paid} for Invoice {invoice_no} via {payment_method}. Balance: {balance_due}.",
                'email_subject'       => "Payment Receipt for Invoice ({invoice_no})",
                'email_body'          => "Dear {customer_name},<br><br>Thank you! We have received your payment of <strong>{amount_paid}</strong> via <strong>{payment_method}</strong> for Invoice <strong>{invoice_no}</strong>.<br><br>Remaining Balance: <strong>{balance_due}</strong>.",
                'available_variables' => ['customer_name', 'invoice_no', 'amount_paid', 'payment_method', 'balance_due'],
            ],
            'payment_overdue' => [
                'title'               => 'Overdue Payment Reminder',
                'category'            => 'invoices',
                'whatsapp_template'   => "Hello {customer_name},\n\nThis is a gentle reminder that Invoice *{invoice_no}* with pending amount *{amount_due}* is overdue by *{days_overdue} days*.\n\nKindly arrange for clearance at your earliest convenience.",
                'sms_template'        => "Reminder: Invoice {invoice_no} for {amount_due} is overdue by {days_overdue} days. Please clear payment.",
                'email_subject'       => "Overdue Payment Notice: Invoice ({invoice_no})",
                'email_body'          => "Dear {customer_name},<br><br>This is a reminder that payment of <strong>{amount_due}</strong> for Invoice <strong>{invoice_no}</strong> is overdue by <strong>{days_overdue} days</strong>.<br><br>Please settle the outstanding balance.",
                'available_variables' => ['customer_name', 'invoice_no', 'amount_due', 'days_overdue'],
            ],

            // 8. Expense Claims Module
            'expense_submitted' => [
                'title'               => 'Staff Expense Claim Submitted',
                'category'            => 'expenses',
                'whatsapp_template'   => "Hello {employee_name},\n\nYour expense claim *{expense_number}* for *{amount}* ({category}) on {expense_date} has been submitted for approval.\n\nDescription: {description}",
                'sms_template'        => "Expense claim {expense_number} for {amount} submitted for management review.",
                'email_subject'       => "Expense Claim Submitted ({expense_number})",
                'email_body'          => "Hello {employee_name},<br><br>Your expense claim <strong>{expense_number}</strong> for <strong>{amount}</strong> ({category}) has been submitted for approval.<br><br>Description: {description}",
                'available_variables' => ['employee_name', 'expense_number', 'amount', 'category', 'expense_date', 'description'],
            ],
            'expense_status_updated' => [
                'title'               => 'Expense Claim Status Update',
                'category'            => 'expenses',
                'whatsapp_template'   => "Hello {employee_name},\n\nYour expense claim *{expense_number}* (Amount: *{amount}*) has been *{status}* by {actioner_name}.\n\nRemarks: {review_notes}",
                'sms_template'        => "Expense claim {expense_number} for {amount} has been {status}.",
                'email_subject'       => "Expense Claim {status}: {expense_number}",
                'email_body'          => "Hello {employee_name},<br><br>Your expense claim <strong>{expense_number}</strong> of <strong>{amount}</strong> has been <strong>{status}</strong> by {actioner_name}.<br><br>Remarks: {review_notes}",
                'available_variables' => ['employee_name', 'expense_number', 'amount', 'category', 'status', 'actioner_name', 'review_notes'],
            ],

            // 9. Leave Management Module
            'leave_submitted' => [
                'title'               => 'Leave Request Submitted',
                'category'            => 'leave',
                'whatsapp_template'   => "Hello {employee_name},\n\nYour leave application for *{leave_type}* ({days_count} day(s) from {start_date} to {end_date}) has been submitted for approval.",
                'sms_template'        => "Leave request for {days_count} day(s) ({start_date} to {end_date}) submitted for review.",
                'email_subject'       => "Leave Request Submitted: {leave_type}",
                'email_body'          => "Hello {employee_name},<br><br>Your leave request for <strong>{leave_type}</strong> from <strong>{start_date}</strong> to <strong>{end_date}</strong> ({days_count} day(s)) has been submitted.",
                'available_variables' => ['employee_name', 'leave_type', 'start_date', 'end_date', 'days_count', 'reason'],
            ],
            'leave_status_updated' => [
                'title'               => 'Leave Request Status Update',
                'category'            => 'leave',
                'whatsapp_template'   => "Hello {employee_name},\n\nYour leave application for *{leave_type}* ({start_date} to {end_date}) has been *{status}* by {actioner_name}.\n\nRemarks: {review_remarks}",
                'sms_template'        => "Leave request ({start_date} to {end_date}) has been {status}.",
                'email_subject'       => "Leave Request {status}: {leave_type}",
                'email_body'          => "Hello {employee_name},<br><br>Your leave request from <strong>{start_date}</strong> to <strong>{end_date}</strong> has been <strong>{status}</strong> by {actioner_name}.<br><br>Remarks: {review_remarks}",
                'available_variables' => ['employee_name', 'leave_type', 'start_date', 'end_date', 'days_count', 'status', 'actioner_name', 'review_remarks'],
            ],

            // 10. Payroll & Salary Module
            'payslip_generated' => [
                'title'               => 'Monthly Payslip Generated',
                'category'            => 'payroll',
                'whatsapp_template'   => "Hello {employee_name},\n\nYour payslip *{payroll_number}* for *{period_month}* has been generated.\n\n💵 Net Salary: *{net_salary}*\n(Basic: {basic_pay}, Allowances: {allowances}, Deductions: {deductions})\n\n📄 View Payslip: {payslip_link}",
                'sms_template'        => "Payslip {payroll_number} for {period_month} generated. Net Salary: {net_salary}. View: {payslip_link}",
                'email_subject'       => "Payslip Issued for {period_month} ({payroll_number})",
                'email_body'          => "Dear {employee_name},<br><br>Your salary slip <strong>{payroll_number}</strong> for the month of <strong>{period_month}</strong> is ready.<br><br><strong>Net Salary:</strong> {net_salary}<br><br><a href='{payslip_link}'>View and Download Payslip</a>.",
                'available_variables' => ['employee_name', 'payroll_number', 'period_month', 'net_salary', 'basic_pay', 'allowances', 'deductions', 'payslip_link'],
            ],
            'salary_disbursed' => [
                'title'               => 'Salary Disbursal & Credited',
                'category'            => 'payroll',
                'whatsapp_template'   => "Hello {employee_name},\n\nYour salary of *{net_salary}* for *{period_month}* (Slip: {payroll_number}) has been disbursed via *{payment_method}*.\n\nReference: {payment_reference}",
                'sms_template'        => "Salary {net_salary} for {period_month} disbursed via {payment_method}. Ref: {payment_reference}.",
                'email_subject'       => "Salary Disbursed for {period_month}",
                'email_body'          => "Dear {employee_name},<br><br>We are pleased to inform you that your net salary of <strong>{net_salary}</strong> for <strong>{period_month}</strong> has been credited via <strong>{payment_method}</strong>.<br><br>Transaction Reference: <code>{payment_reference}</code>",
                'available_variables' => ['employee_name', 'payroll_number', 'period_month', 'net_salary', 'payment_method', 'payment_reference'],
            ],

            // 11. Purchase Orders & Procurement Module
            'po_created' => [
                'title'               => 'Purchase Order Issued to Supplier',
                'category'            => 'purchases',
                'whatsapp_template'   => "Hello {supplier_name},\n\nWe have issued Purchase Order *{po_number}* with total amount *{total_amount}* ({item_count} items).\n\nExpected Delivery: {expected_delivery_date}\n📄 View Purchase Order: {po_link}",
                'sms_template'        => "PO {po_number} for {total_amount} issued. Expected: {expected_delivery_date}. View: {po_link}",
                'email_subject'       => "Purchase Order Issued ({po_number})",
                'email_body'          => "Dear {supplier_name},<br><br>Please find Purchase Order <strong>{po_number}</strong> for <strong>{total_amount}</strong> ({item_count} items).<br><br>Expected Delivery Date: <strong>{expected_delivery_date}</strong>.<br><br><a href='{po_link}'>View Purchase Order Details</a>.",
                'available_variables' => ['supplier_name', 'po_number', 'order_date', 'total_amount', 'item_count', 'expected_delivery_date', 'po_link'],
            ],
            'po_goods_received' => [
                'title'               => 'Goods Received Note (GRN) Acknowledged',
                'category'            => 'purchases',
                'whatsapp_template'   => "Hello {supplier_name},\n\nWe have received the delivery for Purchase Order *{po_number}* (Status: *{received_status}*).\n\nThank you for the prompt fulfillment!",
                'sms_template'        => "Delivery for PO {po_number} received (Status: {received_status}).",
                'email_subject'       => "Goods Receipt Acknowledged ({po_number})",
                'email_body'          => "Dear {supplier_name},<br><br>This is to confirm receipt of materials for Purchase Order <strong>{po_number}</strong> on <strong>{received_date}</strong>.<br><br>Receipt Status: <strong>{received_status}</strong>.",
                'available_variables' => ['supplier_name', 'po_number', 'total_amount', 'received_status', 'received_date'],
            ],

            // 12. RMA & Hardware Replacement Module
            'rma_dispatched' => [
                'title'               => 'RMA Unit Dispatched to Vendor',
                'category'            => 'rma',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour faulty CCTV hardware (S/N: *{serial_number}*) under RMA *{rma_no}* has been dispatched to the vendor service center via *{carrier}* (AWB: *{awb_no}*).\n\nWe are tracking the repair progress.",
                'sms_template'        => "RMA {rma_no}: Faulty hardware S/N {serial_number} dispatched to vendor via {carrier} (AWB: {awb_no}).",
                'email_subject'       => "Hardware RMA Update ({rma_no})",
                'email_body'          => "Dear {customer_name},<br><br>Your faulty hardware (S/N: <strong>{serial_number}</strong>) under RMA <strong>{rma_no}</strong> has been shipped to the manufacturer service center via <strong>{carrier}</strong> (AWB: {awb_no}).",
                'available_variables' => ['customer_name', 'rma_no', 'serial_number', 'carrier', 'awb_no'],
            ],

            // 13. Inventory & Stock Alerts Module
            'stock_low_threshold_alert' => [
                'title'               => 'Low Stock Inventory Alert',
                'category'            => 'inventory',
                'whatsapp_template'   => "⚠️ *INVENTORY LOW STOCK ALERT*\n\nProduct: *{product_name}* (SKU: {sku})\nCategory: {category}\nCurrent Stock: *{current_stock} {unit}* (Min Threshold: {min_stock} {unit})\n\n📦 Fast reorder required to prevent project/installation delays:\n{reorder_link}",
                'sms_template'        => "LOW STOCK ALERT: {product_name} (SKU: {sku}) is down to {current_stock} {unit} (Min: {min_stock}). Reorder now: {reorder_link}",
                'email_subject'       => "⚠️ Low Stock Alert: {product_name} ({current_stock} {unit} remaining)",
                'email_body'          => "<strong>Inventory Threshold Warning</strong><br><br>Product <strong>{product_name}</strong> (SKU: <code>{sku}</code>, Category: {category}) has fallen below its minimum safety stock level.<br><br><ul><li><strong>Current Stock:</strong> {current_stock} {unit}</li><li><strong>Minimum Threshold:</strong> {min_stock} {unit}</li></ul><br><a href='{reorder_link}'>Click here to view Inventory & place reorder</a>.",
                'available_variables' => ['product_name', 'sku', 'current_stock', 'min_stock', 'category', 'unit', 'reorder_link', 'customer_name'],
            ],

            // 14. Security & Authentication Module
            'otp_verification' => [
                'title'               => 'Instant OTP Security Verification',
                'category'            => 'general',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour secure verification code for CCTV CRM is: *{otp_code}*.\n\nThis OTP is valid for {expiry_minutes} minutes. Please do not share this code with anyone.",
                'sms_template'        => "Your CCTV CRM OTP is {otp_code}. Valid for {expiry_minutes} mins. Do not share with anyone.",
                'email_subject'       => "Your CCTV CRM Verification Code ({otp_code})",
                'email_body'          => "Dear {customer_name},<br><br>Your one-time verification code is <strong>{otp_code}</strong>.<br><br>This code will expire in {expiry_minutes} minutes.",
                'available_variables' => ['customer_name', 'otp_code', 'expiry_minutes'],
            ],
        ];
    }

    public static function getTemplate(string $eventKey): self
    {
        $template = self::where('event_key', $eventKey)->first();

        if (!$template) {
            $defaults = self::defaultTemplates();
            $data = $defaults[$eventKey] ?? [
                'title'               => ucfirst(str_replace('_', ' ', $eventKey)),
                'category'            => 'general',
                'whatsapp_template'   => "Notification for {customer_name}: {event_key}",
                'sms_template'        => "Notification for {customer_name}: {event_key}",
                'email_subject'       => "CCTV CRM Alert: " . ucfirst(str_replace('_', ' ', $eventKey)),
                'email_body'          => "Notification for {customer_name}.",
                'available_variables' => ['customer_name'],
            ];

            $template = self::create(array_merge(['event_key' => $eventKey], $data));
        }

        return $template;
    }
}
