<?php

namespace App\Helpers;

use App\Models\GatewaySetting;

class GstHelper
{
    public const GSTIN_REGEX = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i';

    public const STATE_CODES = [
        '01' => 'Jammu and Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu',
        '27' => 'Maharashtra',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
    ];

    /**
     * Validate if string matches the official 15-character Indian GSTIN pattern.
     */
    public static function isValidGstin(?string $gstin): bool
    {
        if (empty($gstin)) {
            return false;
        }

        $clean = strtoupper(trim($gstin));
        if (strlen($clean) !== 15) {
            return false;
        }

        return (bool) preg_match(self::GSTIN_REGEX, $clean);
    }

    /**
     * Extract 2-digit state code from GSTIN.
     */
    public static function extractStateCode(?string $gstin): ?string
    {
        if (empty($gstin) || strlen(trim($gstin)) < 2) {
            return null;
        }

        $code = substr(trim($gstin), 0, 2);
        return array_key_exists($code, self::STATE_CODES) ? $code : $code;
    }

    /**
     * Get State name from 2-digit code.
     */
    public static function getStateName(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        return self::STATE_CODES[$code] ?? null;
    }

    /**
     * Get the buyer/company state code from GatewaySetting or default to 33 (Tamil Nadu).
     */
    public static function getCompanyStateCode(): string
    {
        try {
            $settings = GatewaySetting::first();
            if ($settings && !empty($settings->company_state_code)) {
                return (string) $settings->company_state_code;
            }
            if ($settings && !empty($settings->company_gstin)) {
                $derived = self::extractStateCode($settings->company_gstin);
                if ($derived) {
                    return $derived;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return '33'; // Default: Tamil Nadu
    }

    /**
     * Calculate CGST/SGST vs IGST split.
     * When vendorStateCode === companyStateCode -> Intrastate (CGST + SGST)
     * When vendorStateCode !== companyStateCode -> Interstate (IGST)
     */
    public static function calculateTaxSplit(
        float $taxableAmount,
        float $taxRate,
        ?string $vendorStateCode,
        ?string $companyStateCode = null
    ): array {
        $companyCode = $companyStateCode ?: self::getCompanyStateCode();
        $isInterstate = !empty($vendorStateCode) && ($vendorStateCode !== $companyCode);

        $taxableAmount = round($taxableAmount, 2);
        $taxRate = (float) $taxRate;

        if ($isInterstate) {
            $igstRate = $taxRate;
            $igstAmount = round(($taxableAmount * $igstRate) / 100, 2);
            $cgstRate = 0.0;
            $cgstAmount = 0.0;
            $sgstRate = 0.0;
            $sgstAmount = 0.0;
            $totalTax = $igstAmount;
        } else {
            $igstRate = 0.0;
            $igstAmount = 0.0;
            $halfRate = round($taxRate / 2, 2);
            $cgstRate = $halfRate;
            $sgstRate = $halfRate;
            $cgstAmount = round(($taxableAmount * $cgstRate) / 100, 2);
            $sgstAmount = round(($taxableAmount * $sgstRate) / 100, 2);
            $totalTax = round($cgstAmount + $sgstAmount, 2);
        }

        return [
            'is_interstate' => $isInterstate,
            'tax_rate'      => $taxRate,
            'taxable_value' => $taxableAmount,
            'cgst_rate'     => $cgstRate,
            'cgst_amount'   => $cgstAmount,
            'sgst_rate'     => $sgstRate,
            'sgst_amount'   => $sgstAmount,
            'igst_rate'     => $igstRate,
            'igst_amount'   => $igstAmount,
            'total_tax'     => $totalTax,
            'total_amount'  => round($taxableAmount + $totalTax, 2),
        ];
    }
}
