<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['sale_id', 'payment_method_id', 'amount'];

    public function method() {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    // Alias used in receipt view
    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }
}
