<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\ProductImage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use \App\Traits\RedirectIndex;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $product = $this->record;
        $data = $this->form->getState();

        // Handle featured image
        if (!empty($data['featured_image'])) {
            $this->record->featuredImage()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'is_primary' => true, // Match the existing primary image
                ],
                [
                    'image_path' => $data['featured_image'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
