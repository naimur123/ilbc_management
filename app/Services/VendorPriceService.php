<?php

namespace App\Services;

use App\Models\ProductSku;
use App\Models\Vendor;
use App\Models\VendorPriceHistory;
use App\Models\VendorProductPrice;
use App\Services\CostCalculationService;
use App\Services\MarginCalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Section 12/13: vendor product pricing + the automatic cost-comparison
 * table. Prices are never overwritten — updatePrice() always inserts a new
 * row and closes the old one off into vendor_price_history, so historical
 * requests that already snapshotted a price are unaffected (Section 41).
 */
class VendorPriceService
{
    public function __construct(
        private CostCalculationService $costCalc,
        private MarginCalculationService $marginCalc,
    ) {
    }

    /**
     * Insert a brand-new current price for a vendor+SKU, closing off
     * whatever was previously current.
     */
    public function setPrice(Vendor $vendor, ProductSku $sku, array $data): VendorProductPrice
    {
        return DB::transaction(function () use ($vendor, $sku, $data) {
            $previous = VendorProductPrice::where('vendor_id', $vendor->id)
                ->where('product_sku_id', $sku->id)
                ->where('is_current', true)
                ->first();

            if ($previous) {
                $previous->update([
                    'is_current' => false,
                    'effective_to' => $data['effective_from'] ?? now()->toDateString(),
                ]);
            }

            $new = VendorProductPrice::create([
                'vendor_id' => $vendor->id,
                'product_id' => $sku->product_id,
                'product_sku_id' => $sku->id,
                'unit_purchase_price' => $data['unit_purchase_price'],
                'cost_unit' => $data['cost_unit'] ?? $sku->consumption_unit,
                'currency_id' => $data['currency_id'],
                'vat_percent' => $data['vat_percent'] ?? 0,
                'tax_percent' => $data['tax_percent'] ?? 0,
                'other_cost' => $data['other_cost'] ?? 0,
                'handling_cost' => $data['handling_cost'] ?? 0,
                'delivery_cost' => $data['delivery_cost'] ?? 0,
                'effective_from' => $data['effective_from'] ?? now()->toDateString(),
                'effective_to' => $data['effective_to'] ?? null,
                'minimum_quantity' => $data['minimum_quantity'] ?? 1,
                'price_type' => $data['price_type'] ?? 'LIST',
                'is_current' => true,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => Auth::id(),
            ]);

            VendorPriceHistory::create([
                'vendor_product_price_id' => $new->id,
                'vendor_id' => $vendor->id,
                'product_sku_id' => $sku->id,
                'old_unit_purchase_price' => $previous?->unit_purchase_price,
                'new_unit_purchase_price' => $new->unit_purchase_price,
                'effective_from' => $new->effective_from,
                'effective_to' => $new->effective_to,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);

            return $new;
        });
    }

    /**
     * Section 13's comparison table for one SKU at a given quantity: every
     * active vendor price, with base/final cost, profit and margin computed
     * against a selling price, sorted cheapest-final-cost first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function compareForSku(ProductSku $sku, float $quantity, float $unitSellingPrice): array
    {
        $prices = VendorProductPrice::with(['vendor', 'currency'])
            ->where('product_sku_id', $sku->id)
            ->where('is_current', true)
            ->whereHas('vendor', fn ($q) => $q->where('status', 'ACTIVE'))
            ->get();

        $sellingTotal = round($quantity * $unitSellingPrice, 2);

        $rows = $prices->map(function (VendorProductPrice $price) use ($quantity, $sellingTotal) {
            $cost = $this->costCalc->landedCost(
                (float) $price->unit_purchase_price,
                $quantity,
                (float) $price->vat_percent,
                (float) $price->tax_percent,
                (float) $price->handling_cost,
                (float) $price->delivery_cost,
                (float) $price->other_cost,
            );

            return [
                'vendor_product_price_id' => $price->id,
                'vendor' => $price->vendor,
                'unit_cost' => (float) $price->unit_purchase_price,
                'quantity' => $quantity,
                'base_cost' => $cost['base_cost'],
                'vat_amount' => $cost['vat_amount'],
                'tax_amount' => $cost['tax_amount'],
                'other_cost' => (float) $price->other_cost + (float) $price->handling_cost + (float) $price->delivery_cost,
                'final_landed_cost' => $cost['final_landed_cost'],
                'gross_profit' => $this->marginCalc->grossProfit($sellingTotal, $cost['final_landed_cost']),
                'gross_margin_percent' => $this->marginCalc->grossMarginPercent($sellingTotal, $cost['final_landed_cost']),
                'is_best_price' => false,
            ];
        })->sortBy('final_landed_cost')->values();

        if ($rows->isNotEmpty()) {
    $firstRow = $rows->first();
    $firstRow['is_best_price'] = true;

    $rows->put(0, $firstRow);
}


        return $rows->all();
    }
}
