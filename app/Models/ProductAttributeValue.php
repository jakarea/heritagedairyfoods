<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttributeValue extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['product_attribute_id', 'value', 'is_active', 'slug'];

    public function productAttribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_product', 'product_attribute_value_id', 'product_id');
    }
}