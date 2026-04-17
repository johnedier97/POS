<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComponent extends Model
{
    use HasFactory;

    protected $fillable = ['parent_product_id', 'child_product_id', 'quantity'];

    public function childProduct()
    {
        return $this->belongsTo(Product::class, 'child_product_id');
    }
}
