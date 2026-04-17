<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $fillable = ['purchase_order_id', 'product_id', 'quantity', 'unit_cost', 'total_cost'];

    public function purchaseOrder() {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
