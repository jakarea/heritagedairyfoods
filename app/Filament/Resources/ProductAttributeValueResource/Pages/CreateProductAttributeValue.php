<?php

namespace App\Filament\Resources\ProductAttributeValueResource\Pages;

use App\Filament\Resources\ProductAttributeValueResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductAttributeValue extends CreateRecord
{
    use \App\Traits\RedirectIndex;
    
    protected static string $resource = ProductAttributeValueResource::class;
}
