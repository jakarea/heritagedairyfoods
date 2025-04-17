<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'number_of_products',
        'image',
    ];

    /**
     * Get the products associated with this tag.
     */
    // public function products(): BelongsToMany
    // {
    //     return $this->belongsToMany(Product::class, 'tags');
    // }

    /**
     * Get the number of products associated with this tag.
     */
    // public function getNumberOfProductsAttribute(): int
    // {
    //     return $this->products()->count();
    // }
}