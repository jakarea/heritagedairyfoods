<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariation extends Model
{
    protected $fillable = [
        'name',
        'product_id',
        'price',
        'discount_price',
        'discount_in',
        'stock',
        'sku',
        'weight',
        'is_default'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function image(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'variation_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductVariationAttribute::class);
    }

    public function productAttributeValues()
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_variation_attributes',
            'product_variation_id',
            'product_attribute_value_id'
        );
    }
}
