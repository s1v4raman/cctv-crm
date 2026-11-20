<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentSequenceService
{
    /**
     * Generate the next sequential purchase order number safely inside a database transaction with lockForUpdate.
     * Keeps the visible format: PO-YYYYMMDD-XXXX
     */
    public static function nextPurchaseOrderNumber(?string $dateString = null): string
    {
        $date = $dateString ? Carbon::parse($dateString) : now();
        $dateKey = $date->format('Ymd');
        $type = 'purchase_order';

        return DB::transaction(function () use ($type, $dateKey) {
            // Ensure sequence record exists using atomic upsert
            try {
                DB::statement("
                    INSERT INTO document_sequences (type, year, last_number, created_at, updated_at)
                    VALUES (?, ?, 0, NOW(), NOW())
                    ON CONFLICT (type, year) DO NOTHING
                ", [$type, $dateKey]);
            } catch (\Throwable $e) {
                // Fallback for non-postgres or SQLite
                if (!DocumentSequence::where('type', $type)->where('year', $dateKey)->exists()) {
                    try {
                        DocumentSequence::create([
                            'type'        => $type,
                            'year'        => $dateKey,
                            'last_number' => 0,
                        ]);
                    } catch (\Throwable $ignored) {
                    }
                }
            }

            /** @var DocumentSequence $sequence */
            $sequence = DocumentSequence::where('type', $type)
                ->where('year', $dateKey)
                ->lockForUpdate()
                ->first();

            $sequence->increment('last_number');
            $sequence->refresh();

            return 'PO-' . $dateKey . '-' . str_pad((string) $sequence->last_number, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Generate the next sequential Goods Receipt Note number safely inside a database transaction with lockForUpdate.
     * Format: GRN-YYYYMMDD-XXXX
     */
    public static function nextGoodsReceiptNumber(?string $dateString = null): string
    {
        $date = $dateString ? Carbon::parse($dateString) : now();
        $dateKey = $date->format('Ymd');
        $type = 'goods_receipt';

        return DB::transaction(function () use ($type, $dateKey) {
            try {
                DB::statement("
                    INSERT INTO document_sequences (type, year, last_number, created_at, updated_at)
                    VALUES (?, ?, 0, NOW(), NOW())
                    ON CONFLICT (type, year) DO NOTHING
                ", [$type, $dateKey]);
            } catch (\Throwable $e) {
                if (!DocumentSequence::where('type', $type)->where('year', $dateKey)->exists()) {
                    try {
                        DocumentSequence::create([
                            'type'        => $type,
                            'year'        => $dateKey,
                            'last_number' => 0,
                        ]);
                    } catch (\Throwable $ignored) {
                    }
                }
            }

            /** @var DocumentSequence $sequence */
            $sequence = DocumentSequence::where('type', $type)
                ->where('year', $dateKey)
                ->lockForUpdate()
                ->first();

            $sequence->increment('last_number');
            $sequence->refresh();

            return 'GRN-' . $dateKey . '-' . str_pad((string) $sequence->last_number, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Generate the next sequential Vendor Bill number safely inside a database transaction with lockForUpdate.
     * Format: BILL-YYYYMMDD-XXXX
     */
    public static function nextVendorBillNumber(?string $dateString = null): string
    {
        $date = $dateString ? Carbon::parse($dateString) : now();
        $dateKey = $date->format('Ymd');
        $type = 'vendor_bill';

        return DB::transaction(function () use ($type, $dateKey) {
            try {
                DB::statement("
                    INSERT INTO document_sequences (type, year, last_number, created_at, updated_at)
                    VALUES (?, ?, 0, NOW(), NOW())
                    ON CONFLICT (type, year) DO NOTHING
                ", [$type, $dateKey]);
            } catch (\Throwable $e) {
                if (!DocumentSequence::where('type', $type)->where('year', $dateKey)->exists()) {
                    try {
                        DocumentSequence::create([
                            'type'        => $type,
                            'year'        => $dateKey,
                            'last_number' => 0,
                        ]);
                    } catch (\Throwable $ignored) {
                    }
                }
            }

            /** @var DocumentSequence $sequence */
            $sequence = DocumentSequence::where('type', $type)
                ->where('year', $dateKey)
                ->lockForUpdate()
                ->first();

            $sequence->increment('last_number');
            $sequence->refresh();

            return 'BILL-' . $dateKey . '-' . str_pad((string) $sequence->last_number, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Generate the next sequential Purchase Return number safely inside a database transaction with lockForUpdate.
     * Format: PR-YYYYMMDD-XXXX
     */
    public static function nextPurchaseReturnNumber(?string $dateString = null): string
    {
        $date = $dateString ? Carbon::parse($dateString) : now();
        $dateKey = $date->format('Ymd');
        $type = 'purchase_return';

        return DB::transaction(function () use ($type, $dateKey) {
            try {
                DB::statement("
                    INSERT INTO document_sequences (type, year, last_number, created_at, updated_at)
                    VALUES (?, ?, 0, NOW(), NOW())
                    ON CONFLICT (type, year) DO NOTHING
                ", [$type, $dateKey]);
            } catch (\Throwable $e) {
                if (!DocumentSequence::where('type', $type)->where('year', $dateKey)->exists()) {
                    try {
                        DocumentSequence::create([
                            'type'        => $type,
                            'year'        => $dateKey,
                            'last_number' => 0,
                        ]);
                    } catch (\Throwable $ignored) {
                    }
                }
            }

            /** @var DocumentSequence $sequence */
            $sequence = DocumentSequence::where('type', $type)
                ->where('year', $dateKey)
                ->lockForUpdate()
                ->first();

            $sequence->increment('last_number');
            $sequence->refresh();

            return 'PR-' . $dateKey . '-' . str_pad((string) $sequence->last_number, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Generate the next sequential Debit Note number safely inside a database transaction with lockForUpdate.
     * Format: DN-YYYYMMDD-XXXX
     */
    public static function nextDebitNoteNumber(?string $dateString = null): string
    {
        $date = $dateString ? Carbon::parse($dateString) : now();
        $dateKey = $date->format('Ymd');
        $type = 'debit_note';

        return DB::transaction(function () use ($type, $dateKey) {
            try {
                DB::statement("
                    INSERT INTO document_sequences (type, year, last_number, created_at, updated_at)
                    VALUES (?, ?, 0, NOW(), NOW())
                    ON CONFLICT (type, year) DO NOTHING
                ", [$type, $dateKey]);
            } catch (\Throwable $e) {
                if (!DocumentSequence::where('type', $type)->where('year', $dateKey)->exists()) {
                    try {
                        DocumentSequence::create([
                            'type'        => $type,
                            'year'        => $dateKey,
                            'last_number' => 0,
                        ]);
                    } catch (\Throwable $ignored) {
                    }
                }
            }

            /** @var DocumentSequence $sequence */
            $sequence = DocumentSequence::where('type', $type)
                ->where('year', $dateKey)
                ->lockForUpdate()
                ->first();

            $sequence->increment('last_number');
            $sequence->refresh();

            return 'DN-' . $dateKey . '-' . str_pad((string) $sequence->last_number, 4, '0', STR_PAD_LEFT);
        });
    }
}
