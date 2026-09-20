<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\Ticket;

class PricingService
{
    /**
     * Calculate parking fee for a ticket.
     * Returns amount in smallest currency unit (cents).
     */
    public function calculate(Ticket $ticket, Tenant $tenant): array
    {
        $entryAt = $ticket->entry_at;
        $exitAt = now();
        $durationMinutes = (int) $entryAt->diffInMinutes($exitAt);

        $settings = $tenant->settings['parking'] ?? [];
        $ratePerHour = $settings['default_rate_per_hour'] ?? 500; // default $5.00/hr in cents
        $gracePeriod = $settings['grace_period_minutes'] ?? 15;
        $taxRate = $settings['tax_rate'] ?? 0; // percentage * 100 (e.g., 500 = 5%)
        $lostTicketPenalty = $settings['lost_ticket_penalty'] ?? 5000; // $50.00
        $overstaySurcharge = $settings['overstay_surcharge'] ?? 0;

        // Grace period: if within grace, free
        if ($durationMinutes <= $gracePeriod) {
            return [
                'duration_minutes' => $durationMinutes,
                'base_amount' => 0,
                'tax_amount' => 0,
                'surcharge_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
            ];
        }

        // Calculate hours (round up)
        $hours = (int) ceil($durationMinutes / 60);

        // Tiered pricing: find the tier whose threshold covers total hours,
        // then apply that single rate to ALL hours (threshold-based, not cumulative).
        $pricingMode = $settings['pricing_mode'] ?? 'flat';
        $tiers       = $settings['pricing_tiers'] ?? [];

        if ($pricingMode === 'tiered' && ! empty($tiers)) {
            usort($tiers, static function (array $a, array $b): int {
                $aLimit = isset($a['up_to_hours']) ? (int) $a['up_to_hours'] : PHP_INT_MAX;
                $bLimit = isset($b['up_to_hours']) ? (int) $b['up_to_hours'] : PHP_INT_MAX;
                return $aLimit <=> $bLimit;
            });

            foreach ($tiers as $tier) {
                $limit = isset($tier['up_to_hours']) ? (int) $tier['up_to_hours'] : null;
                if ($limit === null || $hours <= $limit) {
                    $ratePerHour = (int) $tier['rate_per_hour'];
                    break;
                }
            }
        }

        // Base calculation
        $baseAmount = (int) ($hours * $ratePerHour);

        // Surcharge for lost ticket
        $surchargeAmount = 0;
        if ($ticket->status->value === 'lost_ticket') {
            $surchargeAmount = $lostTicketPenalty;
        }

        // Overstay surcharge
        $overstayHours = $settings['overstay_after_hours'] ?? 24;
        if ($hours > $overstayHours && $overstaySurcharge > 0) {
            $surchargeAmount += $overstaySurcharge;
        }

        // Tax
        $taxableAmount = $baseAmount + $surchargeAmount;
        $taxAmount = $taxRate > 0 ? (int) round($taxableAmount * $taxRate / 10000) : 0;

        $totalAmount = $baseAmount + $surchargeAmount + $taxAmount;

        return [
            'duration_minutes' => $durationMinutes,
            'base_amount' => $baseAmount,
            'tax_amount' => $taxAmount,
            'surcharge_amount' => $surchargeAmount,
            'discount_amount' => 0,
            'total_amount' => max(0, $totalAmount),
        ];
    }
}
