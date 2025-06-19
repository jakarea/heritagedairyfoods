<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttributeValue extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_attribute_id', 'value', 'is_active', 'slug'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function productAttribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id')->where('is_active', true);
    }

    // public function attribute()
    // {
    //     return $this->belongsTo(ProductAttribute::class, 'product_attribute_id')->where('is_active', true);
    // }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            ProductVariation::class,
            'id', // ProductVariation id (local key on ProductVariationAttribute)
            'id', // Product id
            'id', // This model's id (ProductAttributeValue)
            'product_id' // Foreign key on ProductVariation
        );
    }
}
