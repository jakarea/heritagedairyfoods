<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;

class VariationsRelationManager extends RelationManager
{
    protected static string $relationship = 'variations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('discount_price')
                    ->numeric()
                    ->prefix('$')
                    ->nullable(),
                Forms\Components\Select::make('discount_in')
                    ->options([
                        'flat' => 'Flat',
                        'percentage' => 'Percentage',
                    ])
                    ->default('flat')
                    ->required(),
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(\App\Models\ProductVariation::class, 'sku', ignoreRecord: true),
                Forms\Components\Select::make('attributes')
                    ->multiple()
                    ->relationship('attributes', 'attributeValue.value')
                    ->options(function () {
                        return \App\Models\ProductAttributeValue::with('attribute')->get()
                            ->mapWithKeys(fn($value) => [$value->id => "{$value->attribute->name}: {$value->value}"])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku'),
                Tables\Columns\TextColumn::make('price')
                    ->money('usd'),
                Tables\Columns\TextColumn::make('discount_price')
                    ->money('usd'),
                Tables\Columns\TextColumn::make('stock'),
                Tables\Columns\TextColumn::make('attributes')
                    ->formatStateUsing(fn($state) => $state->isEmpty() ? '' : implode(', ', $state->map(fn($attr) => $attr->attributeValue->value)->toArray())),
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