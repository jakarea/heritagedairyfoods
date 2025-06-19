<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Order extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'session_id',
        'order_by',
        'order_number',
        'payment_id',
        'discount_code',
        'discount_amount',
        'total_price',
        'subtotal_price',
        'shipping_cost',
        'billing_address',
        'billing_phone',
        'shipping_zone',
        'shipped_at',
        'delivered_at',
        'canceled_at',
        'payment_method',
        'status',
    ];

    protected static function booted()
    {
        static::deleting(function ($order) {
            $order->orderItems()->delete();
        });
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
