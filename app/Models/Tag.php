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

    protected $casts = [
        'number_of_products' => 'integer',
    ];

    public function getNumberOfProductsAttribute(): int
    {
        return Product::whereJsonContains('tags', $this->name)->count();
    }
}