<?php

namespace App\Services;

use App\Models\BillingRecord;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Request as WorkRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Section 25: post-audit invoicing. generateInvoice() snapshots the
 * request's items into invoice_items so a later change to request_items
 * never rewrites an already-issued invoice.
 */
class BillingService
{
    public function __construct(private AuditLogService $auditLog)
    {
    }

    public function generateInvoice(WorkRequest $request, array $data): Invoice
    {
        return DB::transaction(function () use ($request, $data) {
            $invoice = Invoice::create([
                'request_id' => $request->id,
                'invoice_no' => $data['invoice_no'] ?? $this->nextInvoiceNumber(),
                'invoice_date' => $data['invoice_date'],
                'billing_month' => $data['billing_month'],
                'billing_period_from' => $data['billing_period_from'] ?? null,
                'billing_period_to' => $data['billing_period_to'] ?? null,
                'invoice_amount' => $data['invoice_amount'],
                'vat_amount' => $data['vat_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'other_charges' => $data['other_charges'] ?? 0,
                'total_amount' => $data['invoice_amount'] + ($data['vat_amount'] ?? 0) + ($data['tax_amount'] ?? 0) + ($data['other_charges'] ?? 0),
                'due_date' => $data['due_date'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'GENERATED',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'request_item_id' => $item->id,
                    'description' => $item->product->name.' ('.$item->sku->sku_code.')',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_selling_price,
                    'line_total' => $item->total_selling_price,
                ]);
            }

            $billingRecord = BillingRecord::firstOrCreate(['request_id' => $request->id]);
            $billingRecord->update(['invoice_generated_at' => now(), 'invoice_generated_by' => Auth::id()]);

            $request->update(['status' => 'INVOICE_GENERATED']);

            $this->auditLog->record('Billing', 'Invoice generated', $request->id, null, $invoice->invoice_no);

            return $invoice;
        });
    }

    public function markInvoiceSent(WorkRequest $request): void
    {
        $request->billingRecord?->update(['invoice_sent_at' => now(), 'invoice_sent_by' => Auth::id()]);
        $request->update(['status' => 'INVOICE_SENT']);
        $this->auditLog->record('Billing', 'Invoice marked sent', $request->id);
    }

    public function markBillingDone(WorkRequest $request): void
    {
        $request->billingRecord?->update(['billing_done_at' => now(), 'billing_done_by' => Auth::id()]);
        $request->update(['status' => 'BILLING_DONE', 'current_stage' => 'CLOSURE', 'current_stage_started_at' => now()]);
        $this->auditLog->record('Billing', 'Billing marked done', $request->id);
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('ym').'-';
        $last = Invoice::where('invoice_no', 'like', $prefix.'%')->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->invoice_no, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
