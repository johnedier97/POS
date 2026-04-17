<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'cost',
        'price',
        'unit_of_measure_id',
        'is_composite'
    ];

    public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function components()
    {
        return $this->hasMany(ProductComponent::class, 'parent_product_id');
    }
}
