<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($variation) {
            if ($variation->is_default) {
                // Set is_default to false for all other variations of the same product
                self::where('product_id', $variation->product_id)
                    ->where('id', '!=', $variation->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function image(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'variation_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductVariationAttribute::class, 'product_variation_id');
    }

    public function directAttributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_variation_attributes', 'product_variation_id', 'product_attribute_id')
            ->withPivot('product_attribute_value_id')
            ->with(['values']);
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
