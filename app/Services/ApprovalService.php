<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\Request as WorkRequest;
use App\Models\VendorSelection;

/**
 * Section 17: configurable conditional approval rules. evaluate() is
 * called when a request enters Reviewer; any rule that fires creates (or
 * keeps) a pending approval_requests row, and Reviewer cannot Approve
 * while any such row is still PENDING (Section 40 Rule mirrors this).
 */
class ApprovalService
{
    public function evaluate(WorkRequest $request): void
    {
        $margin = $request->reviewerApproval?->snapshot_gross_margin_percent;
        $amount = $request->totalSellingPrice();
        $outstanding = (float) $request->customer->outstanding_balance;
        $creditLimit = (float) $request->customer->credit_limit;
        $anyItemNotLowest = $request->items->contains(
            fn ($item) => $item->vendorSelection && ! $item->vendorSelection->is_lowest_cost_vendor
        );

        foreach (ApprovalRule::where('is_active', true)->get() as $rule) {
            $triggered = match ($rule->condition_field) {
                'MARGIN_PERCENT' => $margin !== null && $this->compare((float) $margin, $rule->operator, (float) $rule->threshold_value),
                'SALES_AMOUNT' => $this->compare($amount, $rule->operator, (float) $rule->threshold_value),
                'OUTSTANDING_VS_CREDIT_LIMIT' => $this->compare($outstanding, $rule->operator, $creditLimit),
                'VENDOR_NOT_LOWEST' => $anyItemNotLowest,
                default => false,
            };

            $existing = ApprovalRequest::where('request_id', $request->id)
                ->where('approval_rule_id', $rule->id)
                ->first();

            if ($triggered && ! $existing) {
                ApprovalRequest::create([
                    'request_id' => $request->id,
                    'approval_rule_id' => $rule->id,
                    'status' => 'PENDING',
                ]);
            }
        }
    }

    public function hasPendingApprovals(WorkRequest $request): bool
    {
        return ApprovalRequest::where('request_id', $request->id)->where('status', 'PENDING')->exists();
    }

    private function compare(float $value, string $operator, float $threshold): bool
    {
        return match ($operator) {
            '<' => $value < $threshold,
            '<=' => $value <= $threshold,
            '>' => $value > $threshold,
            '>=' => $value >= $threshold,
            '==' => abs($value - $threshold) < 0.00001,
            '!=' => abs($value - $threshold) >= 0.00001,
            default => false,
        };
    }
}
