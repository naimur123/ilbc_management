<?php

namespace App\Http\Controllers;

use App\Models\CustomerAcknowledge;
use App\Models\CustomerAcknowledgementTerms;
use App\Models\LoadingRecord;
use App\Models\RequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerAcknowledgeController extends Controller
{
    public function index(int|string $customer_id, RequestItem $item, LoadingRecord $record) {
        $item->load([
            'request.customer',
            'product',
            'sku',
            'commitmentType',
            'billingType',
        ]);

        $record->load([
            'commitmentType',
            'billingType',
        ]);

        $validCustomer =
            (int) $item->request?->customer_id
            === (int) $customer_id;

        $validRecord =
            (int) $record->request_item_id
            === (int) $item->id;

        if (!$validCustomer || !$validRecord) {
            abort(
                404,
                'No matching acknowledgement record was found.'
            );
        }

        $alreadyAcknowledged =
            CustomerAcknowledge::query()
                ->where(
                    'customer_id',
                    $customer_id
                )
                ->where(
                    'request_item_id',
                    $item->id
                )
                ->where(
                    'loading_record_id',
                    $record->id
                )
                ->exists();

        $terms =
            CustomerAcknowledgementTerms::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'id'
                )
                ->get();

        $declaration =
            'We hereby confirm that the information provided in this License Acknowledgement Form is accurate and complete. We acknowledge and accept the applicable licensing, subscription, usage, payment, and service terms and agree to use the licensed products and services only for lawful and authorized purposes.';

        return view(
            'customer-ack.view',
            compact(
                'customer_id',
                'item',
                'record',
                'alreadyAcknowledged',
                'terms',
                'declaration'
            )
        );
    }

    public function addCustomerAck(Request $request)
    {
        $validated =
            $request->validate(
                [
                    'customer_id' => [
                        'required',
                        'integer',
                    ],

                    'request_item_id' => [
                        'required',
                        'integer',
                        'exists:request_items,id',
                    ],

                    'loading_record_id' => [
                        'required',
                        'integer',
                        'exists:loading_records,id',
                    ],

                    'accept_all_terms' => [
                        'required',
                        'accepted',
                    ],
                ],
                [
                    'accept_all_terms.required' =>
                        'You must accept the Terms & Conditions before submitting.',

                    'accept_all_terms.accepted' =>
                        'You must accept the Terms & Conditions before submitting.',
                ]
            );

        $item =
            RequestItem::with([
                'request.customer',
            ])->findOrFail(
                $validated['request_item_id']
            );

        $record =
            LoadingRecord::findOrFail(
                $validated['loading_record_id']
            );

        $validCustomer =
            (int) $item->request?->customer_id
            === (int) $validated['customer_id'];

        $validRecord =
            (int) $record->request_item_id
            === (int) $item->id;

        if (
            ! $validCustomer ||
            ! $validRecord
        ) {
            abort(
                404,
                'No matching acknowledgement record was found.'
            );
        }

        $existing =
            CustomerAcknowledge::query()
                ->where(
                    'customer_id',
                    $validated['customer_id']
                )
                ->where(
                    'request_item_id',
                    $item->id
                )
                ->where(
                    'loading_record_id',
                    $record->id
                )
                ->first();

        if ($existing) {
            return redirect()
                ->route(
                    'customer_ack',
                    [
                        'customer_id' =>
                            $validated['customer_id'],

                        'item' =>
                            $item->id,

                        'record' =>
                            $record->id,
                    ]
                )
                ->with(
                    'warning',
                    'You already acknowledged this record.'
                );
        }

        $terms =
            CustomerAcknowledgementTerms::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'id'
                )
                ->get();

        if ($terms->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'accept_all_terms' =>
                        'Terms & Conditions are unavailable. Please contact the service provider.',
                ]);
        }

        $declaration =
            'We hereby confirm that the information provided in this License Acknowledgement Form is accurate and complete. We acknowledge and accept the applicable licensing, subscription, usage, payment, and service terms and agree to use the licensed products and services only for lawful and authorized purposes.';

        $termsSnapshot = [
            'company_name' =>
                $item->request->customer?->name,

            'declaration' =>
                $declaration,

            'terms' =>
                $terms
                    ->map(
                        function (
                            CustomerAcknowledgementTerms $term
                        ) {
                            return [
                                'title' =>
                                    $term->title,

                                'description' =>
                                    $term->description,

                                'sort_order' =>
                                    $term->sort_order,
                            ];
                        }
                    )
                    ->values()
                    ->all(),
        ];

        DB::transaction(
            function () use (
                $validated,
                $item,
                $record,
                $termsSnapshot
            ) {
                $existing =
                    CustomerAcknowledge::query()
                        ->where(
                            'customer_id',
                            $validated['customer_id']
                        )
                        ->where(
                            'request_item_id',
                            $item->id
                        )
                        ->where(
                            'loading_record_id',
                            $record->id
                        )
                        ->lockForUpdate()
                        ->exists();

                if ($existing) {
                    return;
                }

                CustomerAcknowledge::create([
                    'customer_id' =>
                        $validated['customer_id'],

                    'request_item_id' =>
                        $item->id,

                    'loading_record_id' =>
                        $record->id,

                    'terms_accepted' =>
                        true,

                    'terms_snapshot' =>
                        $termsSnapshot,

                    'terms_accepted_at' =>
                        now(),
                ]);
            }
        );

        return redirect()
            ->route(
                'customer_ack',
                [
                    'customer_id' =>
                        $validated['customer_id'],

                    'item' =>
                        $item->id,

                    'record' =>
                        $record->id,
                ]
            )
            ->with(
                'success',
                'Acknowledgement submitted successfully.'
            );
    }
}