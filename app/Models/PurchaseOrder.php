<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = ['supplier_id', 'branch_id', 'date', 'status', 'total'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function details() {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }
}
