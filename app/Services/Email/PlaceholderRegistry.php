<?php

namespace App\Services\Email;

class PlaceholderRegistry
{
    /**
     * Return allowed placeholders and labels.
     */
    public function all(): array
    {
        return [
            '@company' => 'Company Name',
            '@company_logo' => 'Company Logo URL',
            '@company_email' => 'Company Email',
            '@customer_name' => 'Customer Name',
            '@customer_email' => 'Customer Email',
            '@first_name' => 'First Name',
            '@last_name' => 'Last Name',
            '@full_name' => 'Full Name',
            '@phone' => 'Call Center/ Support Phone',
            '@email' => 'User Email',
            '@user_phone' => 'User Phone',
            '@solution_name' => 'Solution name',
            '@quantity' => 'Quantity',
            '@order_Type' => 'Order Type',
            '@commitment' => 'Commitment',
            '@billing_type' => 'Billing Type',
            '@start_date' => 'Start Date',
            '@end_date' => 'End date',
            '@customer_review_link' => 'Customer Review Link'
        ];
    }

    public function keys(): array
    {
        return array_keys($this->all());
    }

    public function isAllowed(string $placeholder): bool
    {
        return in_array($placeholder, $this->keys(), true);
    }
}