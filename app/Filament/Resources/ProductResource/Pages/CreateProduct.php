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

            // Handle categories
            if (!empty($data['categories']) && is_array($data['categories'])) {
                Category::whereIn('id', $data['categories'])->update([
                    'number_of_products' => DB::raw('COALESCE(number_of_products, 0) + 1'),
                ]);
            }

            // Handle tags
            if (!empty($data['tags']) && is_array($data['tags'])) {
                Tag::whereIn('id', $data['tags'])->update([
                    'number_of_products' => DB::raw('COALESCE(number_of_products, 0) + 1'),
                ]);
            }

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
                        foreach ($data['product_attributes'] as $attributeSet) {
                            foreach ($attributeSet['product_attribute_values'] as $value) {
                                $variationAttributes[] = [
                                    'product_variation_id' => $variation->id,
                                    'product_attribute_id' => $attributeSet['product_attribute_id'],
                                    'product_attribute_value_id' => $value,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
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

        // Handle product attributes
        // if (!empty($data['attributes'])) {
        //     foreach ($data['attributes'] as $attributeData) {
        //         if (!empty($attributeData['product_attribute_id']) && !empty($attributeData['product_attribute_values'])) {
        //             foreach ($attributeData['product_attribute_values'] as $value) {
        //                 ProductAttributeValue::create([
        //                     'product_attribute_id' => $attributeData['product_attribute_id'],
        //                     'value' => $value,
        //                 ]);
        //             }
        //         }
        //     }
        // }

    }
}
