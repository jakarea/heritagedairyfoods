<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->required()
                    ->image()
                    ->directory('product-images')
                    ->preserveFilenames(),
                Forms\Components\Toggle::make('is_primary')
                    ->default(false),
                Forms\Components\Select::make('variation_id')
                    ->label('Variation')
                    ->options(function (callable $get) {
                        return \App\Models\ProductVariation::where('product_id', $get('product_id'))->pluck('sku', 'id');
                    })
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('image_path')
                    ->disk('public')
                    ->height(100),
                Tables\Columns\BooleanColumn::make('is_primary')->label('Featured'),
                Tables\Columns\BooleanColumn::make('variation_id')
                    ->label('Variation')
                    ->getStateUsing(fn($record) => !is_null($record->variation_id))
                    ->trueIcon('heroicon-o-check-circle') // Green check
                    ->falseIcon('heroicon-o-x-circle')    // Red cross
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\BooleanColumn::make('gallery_id')
                    ->label('Gallery Image')
                    ->getStateUsing(fn($record) => is_null($record->variation_id) && $record->is_primary === false)
                    ->trueIcon('heroicon-o-check-circle') // Green check
                    ->falseIcon('heroicon-o-x-circle')    // Red cross
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
