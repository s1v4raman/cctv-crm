<?php

namespace App\Support;

/**
 * Rupee amounts in Indian digit grouping: ₹12,34,567 (lakh / crore), matching the
 * dashboard's en-IN formatting in the browser.
 */
class Inr
{
    public static function format(float|int|string|null $amount, int $decimals = 0): string
    {
        $amount = (float) $amount;
        $sign = $amount < 0 ? '-' : '';
        [$whole, $fraction] = array_pad(explode('.', number_format(abs($amount), $decimals, '.', '')), 2, null);

        $lastThree = substr($whole, -3);
        $rest = substr($whole, 0, -3);
        if ($rest !== '') {
            $lastThree = preg_replace('/\B(?=(\d{2})+$)/', ',', $rest) . ',' . $lastThree;
        }

        return $sign . '₹' . $lastThree . ($fraction !== null ? '.' . $fraction : '');
    }

    /** Whole rupees, with paise only when the amount has some: ₹1,60,000 or ₹1,234.50. */
    public static function money(float|int|string|null $amount): string
    {
        return self::format($amount, fmod(round((float) $amount, 2), 1) != 0.0 ? 2 : 0);
    }
}
