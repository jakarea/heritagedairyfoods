<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariation;
use App\Models\ProductVariationAttribute;
use App\Models\Tag;
use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use \App\Traits\RedirectIndex;
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $product = $this->record;
            $data = $this->form->getState();

            // Increment number_of_products for categories and tags
            Product::incrementProductCounts(
                $data['categories'] ?? [],
                $data['tags'] ?? []
            );

            // Prepare images
            $imagesToInsert = [];

            // Featured image
            if (!empty($data['featured_image'])) {
                $imagesToInsert[] = [
                    'product_id' => $product->id,
                    'image_path' => $data['featured_image'],
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Gallery images
            if (!empty($data['gallery_images'])) {
                foreach ($data['gallery_images'] as $image) {
                    $imagesToInsert[] = [
                        'product_id' => $product->id,
                        'image_path' => $image,
                        'is_primary' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($imagesToInsert)) {
                ProductImage::insert($imagesToInsert);
            }

            // Variations
            if (!empty($data['product_variations'])) {
                foreach ($data['product_variations'] as $index => $variationData) {
                    $variation = ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $variationData['name'],
                        'price' => $variationData['price'],
                        'discount_price' => $variationData['discount_price'],
                        'discount_in' => $variationData['discount_in'],
                        'stock' => $variationData['stock'],
                        'sku' => $variationData['sku'],
                        'weight' => $variationData['weight'],
                        'is_default' => $index === 0,
                    ]);

                    // Variation attributes
                    $variationAttributes = [];

                    if (!empty($data['product_attributes'])) {
                        $variationAttributes = [];
                        
                        // Iterate through each variation
                        foreach ($data['variations'] as $variation) {
                            $attributeMap = []; // To store unique attribute-value pairs for this variation
                            
                            // Process each attribute set
                            foreach ($data['product_attributes'] as $attributeSet) {
                                // Ensure only one value is selected per attribute for this variation
                                if (!empty($attributeSet['product_attribute_values'])) {
                                    // Take the first value or ensure a single value is selected
                                    $value = $attributeSet['product_attribute_values'][0];
                                    
                                    // Store the attribute-value pair
                                    $attributeMap[$attributeSet['product_attribute_id']] = [
                                        'product_variation_id' => $variation->id,
                                        'product_attribute_id' => $attributeSet['product_attribute_id'],
                                        'product_attribute_value_id' => $value,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                }
                            }
                            
                            // Add the unique attribute-value pairs for this variation to the collection
                            $variationAttributes = array_merge($variationAttributes, array_values($attributeMap));
                        }
                    }

                    if (!empty($variationAttributes)) {
                        ProductVariationAttribute::insert($variationAttributes);
                    }

                    // Variation image
                    if (!empty($variationData['image'])) {
                        ProductImage::create([
                            'product_id' => $product->id,
                            'variation_id' => $variation->id,
                            'image_path' => $variationData['image'],
                            'is_primary' => false,
                        ]);
                    }
                }
            }
        }); 

    }
}
