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
                Forms\Components\FileUpload::make('image_url')
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
                Tables\Columns\ImageColumn::make('image_url')
                    ->disk('public')
                    ->height(100),
                Tables\Columns\BooleanColumn::make('is_primary'),
                Tables\Columns\TextColumn::make('variation.sku')
                    ->label('Variation'),
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