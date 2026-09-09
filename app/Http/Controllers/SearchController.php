<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Request as WorkRequest;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $requests = collect();
        $invoices = collect();

        if ($q !== '') {
            $requests = WorkRequest::with('customer')
                ->where('request_no', 'like', "%{$q}%")
                ->orWhere('work_order_no', 'like', "%{$q}%")
                ->orWhere('po_number', 'like', "%{$q}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")->orWhere('mobile', 'like', "%{$q}%"))
                ->orWhereHas('items', fn ($i) => $i->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('sku', fn ($s) => $s->where('sku_code', 'like', "%{$q}%")))
                ->limit(30)->get();

            $invoices = Invoice::where('invoice_no', 'like', "%{$q}%")->with('request')->limit(20)->get();
        }

        return view('search.results', compact('q', 'requests', 'invoices'));
    }
}
