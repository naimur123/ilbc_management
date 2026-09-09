<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function viewCost(User $user): bool
    {
        // Vendor purchase price / final cost / profit / margin are gated
        // separately from plain "view" (Section 38: sensitive info control).
        return $user->can('sales.view_cost');
    }

    public function manageApi(User $user): bool
    {
        return $user->can('vendor.api_manage');
    }
}
