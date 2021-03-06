<?php

namespace Point\PointSales\Models\Service;

use Illuminate\Database\Eloquent\Model;
use Point\Framework\Traits\FormulirTrait;

class PaymentCollectionDetail extends Model
{
    use FormulirTrait;

    protected $table = 'point_sales_service_payment_collection_detail';
    public $timestamps = false;

    public function paymentOrder()
    {
        return $this->belongsTo('\Point\PointSales\Models\Service\PaymentCollection', 'point_sales_service_payment_collection_id');
    }

    public function scopeJoinPaymentCollection($q)
    {
        $q->join('point_sales_service_payment_collection', 'point_sales_service_payment_collection.id', '=', 'point_sales_service_payment_collection_id');
    }
}
