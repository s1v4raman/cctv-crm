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
            'job_scheduled' => [
                'title'               => 'Installation Job Scheduled',
                'category'            => 'jobs',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour CCTV installation job *{job_no}* has been scheduled for *{scheduled_date}*.\n\nAttending Lead Technician: *{technician_name}* ({technician_phone}).\n\nOur team will arrive on time with the equipment.",
                'sms_template'        => "Dear {customer_name}, CCTV installation job {job_no} is scheduled for {scheduled_date}. Tech: {technician_name} ({technician_phone}).",
                'email_subject'       => "CCTV Installation Scheduled ({job_no})",
                'email_body'          => "Dear {customer_name},<br><br>We are pleased to inform you that your CCTV installation job <strong>{job_no}</strong> is scheduled for <strong>{scheduled_date}</strong>.<br><br>Assigned Lead Technician: <strong>{technician_name}</strong> (Phone: {technician_phone}).",
                'available_variables' => ['customer_name', 'job_no', 'scheduled_date', 'technician_name', 'technician_phone'],
            ],
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
            'otp_verification' => [
                'title'               => 'Instant OTP Security Verification',
                'category'            => 'general',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour secure verification code for CCTV CRM is: *{otp_code}*.\n\nThis OTP is valid for {expiry_minutes} minutes. Please do not share this code with anyone.",
                'sms_template'        => "Your CCTV CRM OTP is {otp_code}. Valid for {expiry_minutes} mins. Do not share with anyone.",
                'email_subject'       => "Your CCTV CRM Verification Code ({otp_code})",
                'email_body'          => "Dear {customer_name},<br><br>Your one-time verification code is <strong>{otp_code}</strong>.<br><br>This code will expire in {expiry_minutes} minutes.",
                'available_variables' => ['customer_name', 'otp_code', 'expiry_minutes'],
            ],
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
            'rma_dispatched' => [
                'title'               => 'RMA Unit Dispatched to Vendor',
                'category'            => 'rma',
                'whatsapp_template'   => "Hello {customer_name},\n\nYour faulty CCTV hardware (S/N: *{serial_number}*) under RMA *{rma_no}* has been dispatched to the vendor service center via *{carrier}* (AWB: *{awb_no}*).\n\nWe are tracking the repair progress.",
                'sms_template'        => "RMA {rma_no}: Faulty hardware S/N {serial_number} dispatched to vendor via {carrier} (AWB: {awb_no}).",
                'email_subject'       => "Hardware RMA Update ({rma_no})",
                'email_body'          => "Dear {customer_name},<br><br>Your faulty hardware (S/N: <strong>{serial_number}</strong>) under RMA <strong>{rma_no}</strong> has been shipped to the manufacturer service center via <strong>{carrier}</strong> (AWB: {awb_no}).",
                'available_variables' => ['customer_name', 'rma_no', 'serial_number', 'carrier', 'awb_no'],
            ],
            'stock_low_threshold_alert' => [
                'title'               => 'Low Stock Inventory Alert',
                'category'            => 'inventory',
                'whatsapp_template'   => "⚠️ *INVENTORY LOW STOCK ALERT*\n\nProduct: *{product_name}* (SKU: {sku})\nCategory: {category}\nCurrent Stock: *{current_stock} {unit}* (Min Threshold: {min_stock} {unit})\n\n📦 Fast reorder required to prevent project/installation delays:\n{reorder_link}",
                'sms_template'        => "LOW STOCK ALERT: {product_name} (SKU: {sku}) is down to {current_stock} {unit} (Min: {min_stock}). Reorder now: {reorder_link}",
                'email_subject'       => "⚠️ Low Stock Alert: {product_name} ({current_stock} {unit} remaining)",
                'email_body'          => "<strong>Inventory Threshold Warning</strong><br><br>Product <strong>{product_name}</strong> (SKU: <code>{sku}</code>, Category: {category}) has fallen below its minimum safety stock level.<br><br><ul><li><strong>Current Stock:</strong> {current_stock} {unit}</li><li><strong>Minimum Threshold:</strong> {min_stock} {unit}</li></ul><br><a href='{reorder_link}'>Click here to view Inventory & place reorder</a>.",
                'available_variables' => ['product_name', 'sku', 'current_stock', 'min_stock', 'category', 'unit', 'reorder_link', 'customer_name'],
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
