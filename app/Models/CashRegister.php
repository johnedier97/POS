<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'name', 'is_active'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function currentSession()
    {
        return $this->hasOne(CashRegisterSession::class)->where('status', 'open')->latest();
    }

    public function sessions()
    {
        return $this->hasMany(CashRegisterSession::class);
    }
}
