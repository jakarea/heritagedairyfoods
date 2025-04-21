<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'subtitle', 'slug', 'short_desc', 'description', 'base_price', 'discount_price','discount_in',
        'stock', 'status', 'type', 'weight', 'categories', 'tags', 'video',
        'meta_title', 'meta_description', 'meta_keywords', 'search_keywords', 'is_active'
    ];

    protected $casts = [
        'categories' => 'array',
        'tags' => 'array',   
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];
    

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(ProductBundle::class, 'main_product_id');
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function ProductattributeValues()
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_attribute_product', 'product_id', 'product_attribute_value_id')
            ->withPivot('price_adjustment', 'sku');
    }
    
    // Accessor to fetch Category records
    public function getCategoryRecordsAttribute()
    {
        return Category::whereIn('id', $this->categories ?? [])->get();
    }

    // Accessor to fetch Tag records
    public function getTagRecordsAttribute()
    {
        return Tag::whereIn('id', $this->tags ?? [])->get();
    }
}
