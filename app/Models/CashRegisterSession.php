<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegisterSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id', 
        'user_id', 
        'opened_at', 
        'closed_at', 
        'initial_balance', 
        'final_calculated_balance', 
        'final_reported_balance', 
        'status'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'initial_balance' => 'decimal:2',
        'final_calculated_balance' => 'decimal:2',
        'final_reported_balance' => 'decimal:2',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }
}
