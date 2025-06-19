<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'variation_id',
        'image_path',
        'is_primary',
        'order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    /**
     * Scope for primary (featured) image
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($image) {
            if ($image->is_primary) {
                // Find the current primary image for the same product (if any)
                $currentPrimary = static::where('product_id', $image->product_id)
                    ->where('is_primary', 1)
                    ->where('id', '!=', $image->id)
                    ->first();

                if ($currentPrimary) {
                    $newPath = str_replace('products/featured-images', 'products/gallery-images', $currentPrimary->image_path);

                    if (Storage::disk('public')->exists($currentPrimary->image_path)) {
                        Storage::disk('public')->move($currentPrimary->image_path, $newPath);
                    }

                    // IMPORTANT: use saveQuietly instead of update
                    $currentPrimary->is_primary = 0;
                    $currentPrimary->image_path = $newPath;
                    $currentPrimary->saveQuietly();
                }

                // Force current image to stay is_primary = 1
                $image->is_primary = 1;
            }
        });
    }
}
