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
        'customer_name',
        'customer_phone',
        'customer_address',
        'order_number',
        'total_price',
        'shipping_cost',
        'shipping_zone',
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
}