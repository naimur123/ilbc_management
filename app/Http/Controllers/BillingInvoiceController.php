<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Request as WorkRequest;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BillingInvoiceController extends Controller
{
    public function __construct(private BillingService $billingService)
    {
    }

    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('invoice.view'));

        $requests = WorkRequest::with('customer', 'invoices', 'billingRecord')
            ->when($request->filter === 'generated', fn ($q) => $q->where('status', 'INVOICE_GENERATED'))
            ->when($request->filter === 'sent', fn ($q) => $q->where('status', 'INVOICE_SENT'))
            ->when($request->filter === 'done', fn ($q) => $q->where('status', 'BILLING_DONE'))
            ->when(!$request->filter, fn ($q) => $q->where('status', 'BILLING_PENDING'))
            ->latest()->paginate(20);

        return view('billing-invoice.index', compact('requests'));
    }

    public function show(WorkRequest $request)
    {
        Gate::denyIf(! auth()->user()->can('invoice.view'));
        $request->load('customer', 'items.product', 'items.sku', 'invoices', 'billingRecord');

        return view('billing-invoice.show', compact('request'));
    }

    public function generate(Request $httpRequest, WorkRequest $request)
    {
        Gate::denyIf(! $httpRequest->user()->can('invoice.create'));

        $data = $httpRequest->validate([
            'invoice_no' => 'nullable|string|max:60',
            'invoice_date' => 'required|date',
            'billing_month' => 'required|string|max:20',
            'billing_period_from' => 'nullable|date',
            'billing_period_to' => 'nullable|date',
            'invoice_amount' => 'required|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $this->billingService->generateInvoice($request, $data);

        return redirect()->route('billing-invoice.show', $request)->with('success', 'Invoice generated.');
    }

    public function markSent(Request $httpRequest, WorkRequest $request)
    {
        Gate::denyIf(! $httpRequest->user()->can('invoice.mark_sent'));
        $this->billingService->markInvoiceSent($request);

        return back()->with('success', 'Invoice marked sent.');
    }

    public function markDone(Request $httpRequest, WorkRequest $request)
    {
        Gate::denyIf(! $httpRequest->user()->can('invoice.mark_done'));
        $this->billingService->markBillingDone($request);

        return back()->with('success', 'Billing marked done. Ready for Closure.');
    }

    public function pdf(Invoice $invoice)
    {
        Gate::denyIf(! auth()->user()->can('invoice.view'));
        $invoice->load('request.customer', 'items');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('billing-invoice.pdf', compact('invoice'));

        return $pdf->download($invoice->invoice_no.'.pdf');
    }
}
