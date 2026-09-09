<?php

namespace App\Services;

/**
 * The single source of truth for Section 49's cost formula. Every place
 * that shows a cost comparison, a vendor selection, or a reviewer snapshot
 * MUST go through this service — never re-implement the formula inline.
 */
class CostCalculationService
{
    /**
     * @return array{base_cost:float, vat_amount:float, tax_amount:float, final_landed_cost:float}
     */
    public function landedCost(
        float $vendorUnitPrice,
        float $quantity,
        float $vatPercent,
        float $taxPercent,
        float $handlingCost = 0,
        float $deliveryCost = 0,
        float $otherCost = 0,
    ): array {
        $baseCost = $vendorUnitPrice * $quantity;
        $vatAmount = $baseCost * $vatPercent / 100;
        $taxAmount = $baseCost * $taxPercent / 100;

        $finalLandedCost = $baseCost + $vatAmount + $taxAmount + $handlingCost + $deliveryCost + $otherCost;

        return [
            'base_cost' => round($baseCost, 2),
            'vat_amount' => round($vatAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'final_landed_cost' => round($finalLandedCost, 2),
        ];
    }
}
