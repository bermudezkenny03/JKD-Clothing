<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'tax',
        'discount',
        'shipping_cost',
        'total',
        'order_status_id',
        'shipping_status_id',
        'billing_address',
        'shipping_address',
        'placed_at',
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(Parameter::class, 'order_status_id');
    }

    public function shippingStatus()
    {
        return $this->belongsTo(Parameter::class, 'shipping_status_id');
    }
}
