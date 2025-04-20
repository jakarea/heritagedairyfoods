<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;

class BundlesRelationManager extends RelationManager
{
    protected static string $relationship = 'bundles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('bundled_product_id')
                    ->label('Bundled Product')
                    ->options(\App\Models\Product::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Forms\Components\TextInput::make('discount_percentage')
                    ->numeric()
                    ->suffix('%')
                    ->nullable(),
                Forms\Components\TextInput::make('discount_flat')
                    ->numeric()
                    ->prefix('$')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bundledProduct.name')
                    ->label('Bundled Product'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('discount_flat')
                    ->money('usd'),
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