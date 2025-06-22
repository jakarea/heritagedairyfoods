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
        'order_by', 
        'order_number',
        'payment_id',
        'payment_method', 
        'subtotal', 
        'discount', 
        'shipping_cost', 
        'total', 
        'phone', 
        'shipping_address', 
        'shipped_at', 
        'delivered_at', 
        'canceled_at', 
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
