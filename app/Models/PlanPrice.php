<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'currency_id',
        'price',
        'payment_gateway_plan_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}