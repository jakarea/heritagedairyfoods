<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductAttributeValueResource\Pages;
use App\Models\ProductAttributeValue;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Notifications\Notification; 
use Filament\Forms\Components\{Toggle,Section,Select,TextInput}; 
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class ProductAttributeValueResource extends Resource
{
    protected static ?string $model = ProductAttributeValue::class;
    protected static ?string $navigationBadgeTooltip = 'The number of attribute values';
    protected static ?string $navigationGroup = 'Products Management';
    protected static ?string $navigationLabel = 'Attribute Values';
    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Attribute Details')->schema([
                    Select::make('product_attribute_id')
                        ->label('Attribute')
                        ->relationship('productAttribute', 'name')
                        ->required()
                        ->preload()
                        ->searchable(),
                    TextInput::make('value')
                        ->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state)))
                        ->maxLength(100),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(100)
                        ->unique('product_attribute_values', 'slug', ignoreRecord: true)
                        ->disabled(fn($record) => $record !== null)
                        ->dehydrated(),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Enable to make this value available for products.'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('value')
                    ->sortable()
                    ->searchable(),
                    TextColumn::make('slug')
                    ->sortable(),
                    TextColumn::make('productAttribute.name')
                    ->label('Attribute Name')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),
               
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Active Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->attribute('is_active'),
                SelectFilter::make('product_attribute_id')
                    ->label('Attribute')
                    ->relationship('productAttribute', 'name')
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Attribute Value')
                        ->modalDescription('Are you sure you want to delete this value? It will be moved to the trash.')
                        ->modalButton('Confirm')
                        ->before(function ($record, $action) {
                            if ($record->products()->exists()) {
                                Notification::make()
                                    ->title('Cannot Delete')
                                    ->body('This value is assigned to products. Please remove it from products first.')
                                    ->warning()
                                    ->send();
                                $action->halt();
                            }
                        })
                        ->action(fn($record) => $record->delete()),
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
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($records, $action) {
                        $hasProducts = $records->filter(fn($record) => $record->products()->exists())->isNotEmpty();
                        if ($hasProducts) {
                            Notification::make()
                                ->title('Cannot Delete Values')
                                ->body('Some values are assigned to products. Please remove them from products first.')
                                ->warning()
                                ->send();
                            $action->halt();
                        }
                    }),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('value', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductAttributeValues::route('/'),
            'create' => Pages\CreateProductAttributeValue::route('/create'),
            'edit' => Pages\EditProductAttributeValue::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }
}
