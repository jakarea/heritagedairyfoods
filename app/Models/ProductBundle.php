<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundle extends Model
{
    protected $fillable = [
        'main_product_id', 'bundled_product_id', 'quantity',
        'discount_percentage', 'discount_flat',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'discount_flat' => 'decimal:2',
    ];

    public function mainProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'main_product_id');
    }

    public function bundledProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bundled_product_id');
    }
}