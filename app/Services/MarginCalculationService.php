<?php

namespace App\Services;

/**
 * gross_profit = selling_total - final_landed_cost
 * gross_margin = (gross_profit / selling_total) * 100, with the zero
 * selling-price case (Section 49) handled safely instead of dividing by zero.
 */
class MarginCalculationService
{
    public function grossProfit(float $sellingTotal, float $finalLandedCost): float
    {
        return round($sellingTotal - $finalLandedCost, 2);
    }

    public function grossMarginPercent(float $sellingTotal, float $finalLandedCost): float
    {
        if ($sellingTotal <= 0.0) {
            return 0.0;
        }

        return round($this->grossProfit($sellingTotal, $finalLandedCost) / $sellingTotal * 100, 4);
    }
}
