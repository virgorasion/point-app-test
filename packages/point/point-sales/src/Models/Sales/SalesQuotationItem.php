<?php

namespace Point\PointSales\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Point\Core\Traits\ByTrait;

class SalesQuotationItem extends Model
{
    use ByTrait;

    protected $table = 'point_sales_quotation_item';
    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Item', 'item_id');
    }

    public function allocation()
    {
        return $this->belongsTo('\Point\Framework\Models\Master\Allocation', 'allocation_id');
    }
}
