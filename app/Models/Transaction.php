<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'client_id',
        'gateway_id',
        'product_id',
        'quantity',
        'amount',
        'external_id',
        'status',
        'card_last_numbers',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }
}
