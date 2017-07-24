<?php

namespace Point\PointSales\Helpers;

use Illuminate\Http\Request;
use Point\Framework\Helpers\AllocationHelper;
use Point\Framework\Helpers\ReferHelper;
use Point\PointSales\Models\Service\PaymentCollection;
use Point\PointSales\Models\Service\PaymentCollectionDetail;
use Point\PointSales\Models\Service\PaymentCollectionOther;

class ServicePaymentCollectionHelper
{
    public static function searchList($list_payment_collection, $orderBy, $orderType, $status = 0, $date_from, $date_to, $search)
    {
        if ($status != 'all') {
            $list_payment_collection = $list_payment_collection->where('formulir.form_status', '=', $status ?: 0);
        }
        
        if ($orderBy) {
            $list_payment_collection = $list_payment_collection->orderBy($orderBy, $orderType);
        } else {
            $list_payment_collection = $list_payment_collection->orderByStandard();
        }

        if ($date_from) {
            $list_payment_collection = $list_payment_collection->where('form_date', '>=', date_format_db($date_from, 'start'));
        }

        if ($date_to) {
            $list_payment_collection = $list_payment_collection->where('form_date', '<=', date_format_db($date_to, 'end'));
        }

        if ($search) {
            // search input to database
            $list_payment_collection = $list_payment_collection->where(function ($q) use ($search) {
                $q->where('person.name', 'like', '%'.$search.'%')
                    ->orWhere('formulir.form_number', 'like', '%'.$search.'%');
            });
        }

        return $list_payment_collection;
    }

    public static function create(Request $request, $formulir, $references, $references_account, $references_type, $references_id, $references_amount, $references_amount_original, $references_notes, $references_amount_edit = [])
    {
        $payment_collection = new PaymentCollection;
        $payment_collection->formulir_id = $formulir->id;
        $payment_collection->person_id = $request->input('person_id');
        $payment_collection->payment_type = $request->input('payment_type');
        $payment_collection->save();

        $total = 0;
        for ($i=0 ; $i < count($references) ; $i++) {
            $reference = $references[$i];
            
            $payment_collection_detail = new PaymentCollectionDetail;
            $payment_collection_detail->point_sales_service_payment_collection_id = $payment_collection->id;
            $payment_collection_detail->detail_notes = $references_notes[$i];
            $payment_collection_detail->amount = $references_amount[$i];
            $payment_collection_detail->coa_id = $references_account[$i];
            $payment_collection_detail->form_reference_id = $reference->formulir_id;
            $payment_collection_detail->save();

            $total += $payment_collection_detail->amount;

            ReferHelper::create(
                $references_type[$i],
                $references_id[$i],
                get_class($payment_collection_detail),
                $payment_collection_detail->id,
                get_class($payment_collection),
                $payment_collection->id,
                $payment_collection_detail->amount
            );

            $close_status = ReferHelper::closeStatus(
                $references_type[$i],
                $references_id[$i],
                $references_amount_original[$i],
                $references_amount_edit ? $references_amount_edit[$i] : 0
            );

            formulir_lock($reference->formulir_id, $payment_collection->formulir_id);

            if ($close_status) {
                $reference->formulir->form_status = 1;
                $reference->formulir->save();
            }
        }

        for ($i=0 ; $i < count($request->input('coa_id')) ; $i++) {
            $payment_collection_other = new PaymentCollectionOther;
            $payment_collection_other->point_sales_service_payment_collection_id = $payment_collection->id;
            $payment_collection_other->coa_id = $request->input('coa_id')[$i];
            $payment_collection_other->allocation_id = app('request')->input('allocation_id')[$i];
            $payment_collection_other->other_notes = $request->input('coa_notes')[$i];
            $payment_collection_other->amount = number_format_db($request->input('coa_amount')[$i]);
            $payment_collection_other->save();

            $total += $payment_collection_other->amount;
        }

        $payment_collection->total_payment = $total;
        $payment_collection->save();
        
        return $payment_collection;
    }
}