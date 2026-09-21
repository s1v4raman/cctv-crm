<?php

namespace App\Services;

use App\Models\Lead;
use Symfony\Component\HttpFoundation\Response;

class VcardExportService
{
    /**
     * Generate standard vCard 3.0 string for a Lead/Customer.
     */
    public function generateVcardString(Lead $lead): string
    {
        $fullName = trim($lead->customer_name ?: 'Customer');
        $phone = trim($lead->phone ?: '');
        $email = trim($lead->email ?: '');
        $address = trim($lead->site_address ?: '');
        $company = config('app.name', 'CCTV CRM');

        // Clean name parts
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? $fullName;
        $lastName = $nameParts[1] ?? '';

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            "FN:{$fullName}",
            "N:{$lastName};{$firstName};;;",
        ];

        if (!empty($company)) {
            $lines[] = "ORG:{$company} Client";
        }

        if (!empty($phone)) {
            $lines[] = "TEL;TYPE=CELL,VOICE:{$phone}";
        }

        if (!empty($email)) {
            $lines[] = "EMAIL;TYPE=INTERNET,WORK:{$email}";
        }

        if (!empty($address)) {
            $cleanAddress = str_replace(["\r", "\n"], ' ', $address);
            $lines[] = "ADR;TYPE=WORK:;;{$cleanAddress};;;;";
        }

        $lines[] = "NOTE:Imported from CCTV CRM (Lead #{$lead->id})";
        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Return a downloadable HTTP Response containing the vCard .vcf file.
     */
    public function downloadResponse(Lead $lead): Response
    {
        $vcard = $this->generateVcardString($lead);
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $lead->customer_name ?: 'Contact');
        $filename = "Contact_{$safeName}.vcf";

        return response($vcard, 200, [
            'Content-Type' => 'text/vcard; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
