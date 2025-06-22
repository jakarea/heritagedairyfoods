<?php

namespace App\Filament\Resources\CuponResource\Pages;

use App\Filament\Resources\CuponResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCupon extends CreateRecord
{
    use \App\Traits\RedirectIndex;
    
    protected static string $resource = CuponResource::class;
}
