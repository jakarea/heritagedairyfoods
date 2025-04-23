<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name','slug','is_active'];

    protected $casts = [
        'is_active' => 'boolean', 
    ];

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}