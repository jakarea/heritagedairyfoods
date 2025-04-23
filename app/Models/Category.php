<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes; 

    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'number_of_products', 'image','is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'number_of_products' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    } 

    public function getNumberOfProductsAttribute(): int
    {
        return Product::whereJsonContains('categories', $this->id)->count();
    }
}
