<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkRequest;
use App\Models\Vendor;
use App\Models\VendorSelection;
use App\Services\SLAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        Gate::denyIf(! $request->user()->can('report.view'));

        $requests = WorkRequest::with('customer', 'salesperson')
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->customer_id, fn ($q) => $q->where('customer_id', $request->customer_id))
            ->latest()->get();

        $totalSales = $requests->sum(fn ($r) => $r->totalSellingPrice());

        return view('reports.sales', compact('requests', 'totalSales'));
    }

    public function vendor(Request $request)
    {
        Gate::denyIf(! $request->user()->can('report.view'));

        $vendors = Vendor::withCount('productPrices')->get()->map(function (Vendor $v) {
            $selections = VendorSelection::where('vendor_id', $v->id)->get();
            $v->total_purchase = (float) $selections->sum('final_landed_cost');
            $v->selection_count = $selections->count();
            $v->lowest_cost_hits = $selections->where('is_lowest_cost_vendor', true)->count();

            return $v;
        });

        return view('reports.vendor', compact('vendors'));
    }

    public function profit(Request $request)
    {
        Gate::denyIf(! $request->user()->can('report.view'));

        $month = $request->month ? \Carbon\Carbon::parse($request->month) : now();

        $requests = WorkRequest::with('items.vendorSelection')
            ->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)
            ->get();

        $sales = $requests->sum(fn ($r) => $r->totalSellingPrice());
        $cost = $requests->sum(fn ($r) => $r->items->sum(fn ($i) => $i->vendorSelection->final_landed_cost ?? 0));
        $profit = $sales - $cost;
        $margin = $sales > 0 ? round($profit / $sales * 100, 2) : 0;

        return view('reports.profit', compact('requests', 'sales', 'cost', 'profit', 'margin', 'month'));
    }

    public function sla(Request $request, SLAService $slaService)
    {
        Gate::denyIf(! $request->user()->can('report.view'));

        $requests = WorkRequest::whereNotIn('status', ['CLOSED', 'CANCELLED', 'DRAFT'])->get()
            ->map(function (WorkRequest $r) use ($slaService) {
                $r->sla_status = $slaService->statusFor($r);
                $r->hours_pending = $slaService->hoursPending($r);

                return $r;
            });

        return view('reports.sla', compact('requests'));
    }
}
