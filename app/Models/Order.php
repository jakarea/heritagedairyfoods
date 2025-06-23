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
        'coupon_id',         
        'order_number',         
        'subtotal',         
        'discount_amount',         
        'tax_amount',         
        'shipping_cost',         
        'total',         
        'payment_method',         
        'billing_address',         
        'shipping_address',         
        'order_notes',         
        'shipping_method',         
        'status',         
        'consignment_id',         
        'invoice',         
        'tracking_code',         
        'courier_status',         
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

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }
}
