<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class ReceiptNumberGenerator
{
    public function generate(string $prefix = 'RCP'): string
    {
        $date = now()->format('ymd');

        // withTrashed() ensures soft-deleted receipts are counted so their
        // numbers are never reused. lockForUpdate() serialises concurrent calls
        // that DO find an existing row; the retry in PaymentController handles
        // the rare race where both requests find an empty set simultaneously.
        // Query across ALL tenants — receipt_number has a global unique constraint
        // so the sequence must be globally unique, not per-tenant.
        $lastPayment = Payment::withTrashed()
            ->where('receipt_number', 'like', "{$prefix}-{$date}-%")
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $sequence = $lastPayment
            ? (int) substr($lastPayment->receipt_number, -4) + 1
            : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
