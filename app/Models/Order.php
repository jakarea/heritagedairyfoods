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
        'billing_address_id',
        'shipping_address_id',
        'coupon_id', 
        'subtotal', 
        'discount_amount', 
        'tax_amount', 
        'shipping_cost', 
        'total', 
        'payment_method', 
        'shipping_method', 
        'tracking_number', 
        'tracking_carrier', 
        'order_notes', 
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
