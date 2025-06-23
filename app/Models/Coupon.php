<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'discount_amount',
        'buy_product_ids',
        'get_product_ids',
        'min_cart_value',
        'limits',
        'per_user_limit',
        'users',
        'applies_to_product_ids',
        'applies_to_category_ids',
        'end_date',
        'status',
    ];

    protected $casts = [
        'buy_product_ids' => 'array',
        'get_product_ids' => 'array',
        'users' => 'array',
        'applies_to_product_ids' => 'array',
        'applies_to_category_ids' => 'array',
        'end_date' => 'datetime',
        'discount_amount' => 'decimal:2',
        'min_cart_value' => 'decimal:2',
    ];

    
}
