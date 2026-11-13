<?php

namespace App\Support;

class CsvHelper
{
    /**
     * Sanitize a cell value to prevent CSV / Excel formula injection (CWE-1236).
     * If a cell begins with =, +, -, @, \t, or \r, prepend a single quote (')
     * so spreadsheet software treats it strictly as literal text.
     */
    public static function sanitizeCell(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Sanitize an entire row of CSV fields before writing.
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map([self::class, 'sanitizeCell'], $row);
    }

    /**
     * Safe wrapper around fputcsv that automatically sanitizes all fields.
     */
    public static function writeRow($handle, array $fields, string $separator = ",", string $enclosure = '"', string $escape = "\\"): int|false
    {
        return fputcsv($handle, self::sanitizeRow($fields), $separator, $enclosure, $escape);
    }
}

