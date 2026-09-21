<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Support\Str;

class InboundEmailTicketService
{
    /**
     * Process an inbound customer support email into a CRM Service Ticket.
     */
    public function processEmail(array $data): ServiceTicket
    {
        // 1. Extract sender name, email, and phone
        $fromRaw = $data['from'] ?? $data['sender'] ?? '';
        $senderEmail = $data['email'] ?? $this->extractEmail($fromRaw);
        $senderName = $data['name'] ?? $this->extractName($fromRaw);
        if (empty($senderName)) {
            $senderName = !empty($senderEmail) ? 'Customer (' . $senderEmail . ')' : 'Email Support Customer';
        }

        $subject = trim($data['subject'] ?? 'CCTV Support Request via Email');
        $body = trim($data['body'] ?? $data['text'] ?? $data['html'] ?? 'No description provided in email.');
        $phone = $data['phone'] ?? null;

        // 2. Match or Create Customer Lead
        $lead = null;
        if (!empty($senderEmail)) {
            $lead = Lead::where('email', $senderEmail)->first();
        }

        if (!$lead && !empty($senderEmail)) {
            $user = User::where('email', $senderEmail)->first();
            if ($user && $user->lead_id) {
                $lead = Lead::find($user->lead_id);
            }
        }

        if (!$lead) {
            $lead = Lead::create([
                'customer_name' => $senderName,
                'email'         => $senderEmail,
                'phone'         => $phone ?: 'Not provided (Inbound Email)',
                'source'        => 'email',
                'status'        => 'new',
                'notes'         => 'Lead automatically created from inbound support email.',
            ]);
        }

        // 3. Classify Issue Type from email content
        $issueType = $data['issue_type'] ?? $this->classifyIssueType($subject . ' ' . $body);

        // 4. Classify Priority from email content
        $priority = $data['priority'] ?? $this->classifyPriority($subject . ' ' . $body);

        // 5. Check for active AMC Contract
        $activeAmc = $lead->activeAmcContract()->first();
        $billingType = $activeAmc ? 'warranty_amc' : 'billable';

        // 6. Generate Ticket Number TCK-YYYYMM-XXXX
        $yearMonth = date('Ym');
        $lastTicket = ServiceTicket::where('ticket_no', 'like', "TCK-{$yearMonth}-%")->latest('id')->first();
        $seq = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket->ticket_no);
            $seq = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        }
        $ticketNo = sprintf('TCK-%s-%04d', $yearMonth, $seq);

        $descriptionWithEmailMeta = "--- Inbound Customer Support Email ---\n"
            . "From: " . $senderName . " <" . ($senderEmail ?: 'unknown') . ">\n"
            . "Subject: " . $subject . "\n"
            . "Received At: " . now()->format('d M Y, h:i A') . "\n\n"
            . $body;

        // 7. Create the Service Ticket in CRM
        $ticket = ServiceTicket::create([
            'ticket_no'       => $ticketNo,
            'lead_id'         => $lead->id,
            'amc_contract_id' => $activeAmc?->id,
            'title'           => Str::limit($subject, 250),
            'issue_type'      => $issueType,
            'priority'        => $priority,
            'status'          => 'open',
            'description'     => $descriptionWithEmailMeta,
            'billing_type'    => $billingType,
        ]);

        return $ticket;
    }

    /**
     * Smart heuristic to classify CCTV issue type based on email text.
     */
    public function classifyIssueType(string $text): string
    {
        $lower = strtolower($text);

        if (Str::contains($lower, ['offline', 'no signal', 'black screen', 'video loss', 'blank', 'camera down', 'not displaying', 'not working'])) {
            return 'camera_offline';
        }

        if (Str::contains($lower, ['beep', 'beeping', 'buzzing', 'alarm sound', 'hdd error', 'hard disk', 'disk error'])) {
            return 'dvr_nvr_beep';
        }

        if (Str::contains($lower, ['recording', 'playback', 'not recording', 'footage', 'not saving', 'lost footage', 'history'])) {
            return 'recording_failure';
        }

        if (Str::contains($lower, ['power', 'smps', 'poe', 'adapter', 'spark', 'no power', 'power supply', 'adapter burnt'])) {
            return 'power_supply_issue';
        }

        if (Str::contains($lower, ['network', 'mobile view', 'app offline', 'hik-connect', 'gdmss', 'dmss', 'remote view', 'router', 'ip address'])) {
            return 'network_issue';
        }

        if (Str::contains($lower, ['cable', 'wire', 'rat bite', 'bnc', 'loose wire', 'cable cut', 'damaged wire'])) {
            return 'cable_damaged';
        }

        if (Str::contains($lower, ['blurry', 'blur', 'foggy', 'dirty', 'dark at night', 'night vision', 'lens', 'focus', 'reflection'])) {
            return 'blurry_feed';
        }

        if (Str::contains($lower, ['ptz', 'zoom', 'pan', 'tilt', 'rotation', 'preset', '360'])) {
            return 'ptz_control_issue';
        }

        return 'other';
    }

    /**
     * Smart heuristic to classify priority based on keywords.
     */
    public function classifyPriority(string $text): string
    {
        $lower = strtolower($text);

        if (Str::contains($lower, ['urgent', 'emergency', 'critical', 'theft', 'robbery', 'fire', 'all cameras down', 'complete blackout', 'immediate'])) {
            return 'critical';
        }

        if (Str::contains($lower, ['not recording', 'nvr down', 'no video', 'asap', 'main entrance down', 'cash counter'])) {
            return 'high';
        }

        if (Str::contains($lower, ['cleaning', 'lens wipe', 'quote', 'routine', 'angle adjust', 'minor', 'low priority'])) {
            return 'low';
        }

        return 'medium';
    }

    protected function extractEmail(string $str): ?string
    {
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $str, $matches)) {
            return $matches[0];
        }
        return null;
    }

    protected function extractName(string $str): ?string
    {
        if (preg_match('/^([^<]+)</', $str, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
