<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'subtitle', 'slug', 'description', 'short_desc', 'meta_title', 'meta_description','image',
        'meta_keywords', 'search_keywords', 'price', 'offer_price', 'discount_in', 'stock', 'sku',
        'status', 'type', 'weight', 'categories', 'tags', 'video', 'details', 'conclusion'
    ];

    protected $casts = [
        'categories' => 'array',
        'tags' => 'array',
        'video' => 'array',
        'details' => 'array',
        'conclusion' => 'array',
        'price' => 'decimal:2',
        'offer_price' => 'decimal:2',
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
