<?php
// Relation Manager
namespace App\Filament\Resources\AttributeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Str;
use Filament\Tables\Columns\{TextColumn, BooleanColumn, BadgeColumn};

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('value')
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state)))
                    ->maxLength(100)
                    ->unique('attribute_values', 'value', fn($record) => $record?->attribute_id),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique('attribute_values', 'slug', ignoreRecord: true)
                    ->disabled(fn($record) => $record !== null)
                    ->dehydrated(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Enable to make this value available for products.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                TextColumn::make('value')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->badge()
                    ->date(),
            ])
            ->filters([ 
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Active Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->attribute('is_active'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Attribute Value')
                    ->modalDescription('Are you sure you want to delete this value? It will be moved to the trash.')
                    ->modalButton('Confirm'),
                Tables\Actions\RestoreAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Restore Attribute Value')
                    ->modalDescription('Are you sure you want to restore this value?')
                    ->modalButton('Confirm')
                    ->visible(fn($record) => $record->trashed()),
                Tables\Actions\ForceDeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Attribute Value')
                    ->modalDescription('Are you sure you want to permanently delete this value? This action cannot be undone.')
                    ->modalButton('Confirm')
                    ->visible(fn($record) => $record->trashed()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])
            ->defaultSort('value', 'asc');
    }
}
