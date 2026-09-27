<?php

namespace App\Http\Controllers;

use App\Models\CustomerAcknowledge;
use App\Models\LoadingRecord;
use App\Models\RequestItem;
use App\Models\Request as CusotmerReq;
use Illuminate\Http\Request;

class CustomerAcknowledgeController extends Controller
{
    public function index(int|string $customer_id, RequestItem $item, LoadingRecord $record)
    {
        $validCustomer = CusotmerReq::where('customer_id', $customer_id)
                    ->exists();

        $validItem = RequestItem::where('request_id', $item->id)
                    ->exists();

        $validRecord = LoadingRecord::where('id', $record->id)
            ->where('request_item_id', $item->id)
            ->exists();
    
        if (! $validItem || ! $validRecord || !$validCustomer) {
            abort(404, 'No record found.');
        }
    
        $alreadyAcknowledged = CustomerAcknowledge::where('customer_id', $customer_id)
            ->where('request_item_id', $item->id)
            ->where('loading_record_id', $record->id)
            ->exists();
    
        return view('customer-ack.view', compact(
            'customer_id',
            'item',
            'record',
            'alreadyAcknowledged'
        ));
    }

    public function addCustomerAck(Request $request){

        $validated = $request->validate([
                    'customer_id' => 'required',
                    'request_item_id' => 'required|exists:request_items,id',
                    'loading_record_id' => 'required|exists:loading_records,id',
                    'signatory_name' => 'required|string|max:255',
                    'designation' => 'required|string|max:255',
                    'acknowledgement_date' => 'required|date',
                    'signature' => 'required|string|max:255',
                ]);
    
        $validCustomer = CusotmerReq::where('customer_id', $validated['customer_id'])
                    ->exists();

        $validItem = RequestItem::where('request_id', $validated['request_item_id'])
                    ->exists();

        $validRecord = LoadingRecord::where('id', $validated['loading_record_id'])
            ->where('request_item_id', $validated['request_item_id'])
            ->exists();
    
        if (! $validItem || ! $validRecord || !$validCustomer) {
            abort(404, 'No record found.');
        }
    
        $existing = CustomerAcknowledge::where('customer_id', $validated['customer_id'])
            ->where('request_item_id', $validated['request_item_id'])
            ->where('loading_record_id', $validated['loading_record_id'])
            ->first();
    
        if ($existing) {
            return redirect()->route('customer_ack', [
                'customer_id' => $validated['customer_id'],
                'item' => $validated['request_item_id'],
                'record' => $validated['loading_record_id'],
            ])->with('warning', 'You already acknowledged this record.');
        }
    
        CustomerAcknowledge::create($validated);
    
        return redirect()->route('customer_ack', [
            'customer_id' => $validated['customer_id'],
            'item' => $validated['request_item_id'],
            'record' => $validated['loading_record_id'],
        ])->with('success', 'Acknowledgement submitted successfully.');
        
    }
}
