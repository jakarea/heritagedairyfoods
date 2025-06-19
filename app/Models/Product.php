<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'subtitle',
        'slug',
        'short_desc',
        'description',
        'base_price',
        'discount_price',
        'discount_in',
        'stock',
        'sku',
        'status',
        'type',
        'categories',
        'tags',
        'video_url',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'search_keywords',
        'is_active'
    ];

    protected $casts = [
        'categories' => 'array',
        'tags' => 'array',
        'search_keywords' => 'array',
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    public function featuredImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

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

    public function productAttributes()
    {
         $items = $this->hasManyThrough(
            ProductAttribute::class,
            ProductVariationAttribute::class,
            'product_variation_id',        // Foreign key on pivot table
            'id',                          // Foreign key on ProductAttribute
            'id',                          // Local key on Product
            'product_attribute_id'         // Local key on pivot table
        )->whereIn('product_variation_id', function ($query) {
            $query->select('id')
                ->from('product_variations')
                ->where('product_id', $this->id);
        })->distinct();

        // dd($items);

        return $items;
    }


    public function productAttributeValues()
    {
        return $this->hasManyThrough(
            ProductAttributeValue::class,
            ProductVariationAttribute::class,
            'product_variation_id',
            'id',
            'id',
            'product_attribute_value_id'
        )->whereIn('product_variation_id', function ($query) {
            $query->select('id')
                ->from('product_variations')
                ->where('product_id', $this->id);
        })->distinct();
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

    /**
     * Increment number_of_products for given categories and tags.
     *
     * @param array $categoryIds
     * @param array $tagIds
     * @return void
     */
    public static function incrementProductCounts(array $categoryIds, array $tagIds)
    {
        DB::transaction(function () use ($categoryIds, $tagIds) {
            if (!empty($categoryIds)) {
                Category::whereIn('id', $categoryIds)->update([
                    'number_of_products' => DB::raw('COALESCE(number_of_products, 0) + 1'),
                ]);
                foreach ($categoryIds as $categoryId) {
                    Cache::forget('category_' . $categoryId . '_products');
                }
            }

            if (!empty($tagIds)) {
                Tag::whereIn('id', $tagIds)->update([
                    'number_of_products' => DB::raw('COALESCE(number_of_products, 0) + 1'),
                ]);
                foreach ($tagIds as $tagId) {
                    Cache::forget('tag_' . $tagId . '_products');
                }
            }
        });
    }

    /**
     * Decrement number_of_products for given categories and tags.
     *
     * @param array $categoryIds
     * @param array $tagIds
     * @return void
     */
    public static function decrementProductCounts(array $categoryIds, array $tagIds)
    {
        DB::transaction(function () use ($categoryIds, $tagIds) {
            if (!empty($categoryIds)) {
                Category::whereIn('id', $categoryIds)->update([
                    'number_of_products' => DB::raw('GREATEST(COALESCE(number_of_products, 0) - 1, 0)'),
                ]);
                foreach ($categoryIds as $categoryId) {
                    Cache::forget('category_' . $categoryId . '_products');
                }
            }

            if (!empty($tagIds)) {
                Tag::whereIn('id', $tagIds)->update([
                    'number_of_products' => DB::raw('GREATEST(COALESCE(number_of_products, 0) - 1, 0)'),
                ]);
                foreach ($tagIds as $tagId) {
                    Cache::forget('tag_' . $tagId . '_products');
                }
            }
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            static::decrementProductCounts($product->categories ?? [], $product->tags ?? []);
        });

        static::restored(function ($product) {
            static::incrementProductCounts($product->categories ?? [], $product->tags ?? []);
        });
    }
}
