<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\RelationManagers\ValuesRelationManager;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Filament\Forms\Components\{TextInput, Toggle, Section};
use Filament\Tables\Columns\{TextColumn, BooleanColumn};
use Filament\Tables\Filters\{SelectFilter, TrashedFilter};
use App\Filament\Resources\AttributeResource\Pages;
use App\Models\ProductAttribute;
use Filament\Tables\Actions\ActionGroup;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AttributeResource extends Resource
{
    protected static ?string $model = ProductAttribute::class;
    protected static ?string $navigationBadgeTooltip = 'The number of attributes';
    protected static ?string $navigationGroup = 'Products Management';
    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Attribute Details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state)))
                        ->maxLength(100)
                        ->unique(ProductAttribute::class, 'name', ignoreRecord: true),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(100)
                        ->unique(ProductAttribute::class, 'slug', ignoreRecord: true)
                        ->disabled(fn($record) => $record !== null)
                        ->dehydrated(),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Enable to make this attribute available for products.'),
                ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Attribute Details')
                    ->icon('heroicon-o-tag')
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('edit')
                            ->label('Edit Attribute')
                            ->icon('heroicon-o-pencil')
                            ->url(fn($record) => static::getUrl('edit', ['record' => $record]))
                            ->color('primary'),
                    ])
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Attribute Name')
                            ->weight('bold')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->color('primary')
                            ->extraAttributes(['class' => 'bg-gradient-to-r from-primary-50 to-primary-100 p-3 rounded-lg']),
                        Infolists\Components\TextEntry::make('slug')
                            ->label('Slug')
                            ->icon('heroicon-o-link')
                            ->copyable()
                            ->copyMessage('Slug copied to clipboard!')
                            ->badge()
                            ->color('gray')
                            ->extraAttributes(['class' => 'mt-2']),
                        Infolists\Components\TextEntry::make('values_count')
                            ->label('Number of Values')
                            ->state(fn($record) => $record->values()->count())
                            ->icon('heroicon-o-queue-list')
                            ->suffix(' values')
                            ->numeric()
                            ->formatStateUsing(fn($state) => number_format($state))
                            ->badge()
                            ->color('success')
                            ->extraAttributes(['class' => 'mt-2']),
                        Infolists\Components\TextEntry::make('is_active')
                            ->label('Status')
                            ->formatStateUsing(fn($state) => $state ? 'Active' : 'Inactive')
                            ->badge()
                            ->color(fn($state) => $state ? 'success' : 'danger'),
                    ])
                    ->columns(2)
                    ->extraAttributes(['class' => 'bg-white rounded-xl shadow-sm border border-gray-200']),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->sortable(),
                TextColumn::make('values_count')
                    ->label('Values')
                    ->state(fn($record) => $record->values()->count())
                    ->sortable()
                    ->badge()
                    ->color('info'),
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
                SelectFilter::make('is_active')
                    ->label('Active Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->attribute('is_active'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('success'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Attribute')
                        ->modalDescription('Are you sure you want to delete this attribute? It will be moved to the trash.')
                        ->modalButton('Confirm')
                        ->before(function ($record, $action) {
                            if ($record->values()->exists()) {
                                Notification::make()
                                    ->title('Cannot Delete')
                                    ->body('This attribute has associated values. Please delete them first.')
                                    ->warning()
                                    ->send();
                                $action->halt();
                            }
                        })
                        ->action(fn($record) => $record->delete()),
                    Tables\Actions\RestoreAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Restore Attribute')
                        ->modalDescription('Are you sure you want to restore this attribute?')
                        ->modalButton('Confirm')
                        ->visible(fn($record) => $record->trashed()),
                    Tables\Actions\ForceDeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Attribute')
                        ->modalDescription('Are you sure you want to permanently delete this attribute? This action cannot be undone.')
                        ->modalButton('Confirm')
                        ->visible(fn($record) => $record->trashed()),
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($records, $action) {
                        $hasValues = $records->filter(fn($record) => $record->values()->exists())->isNotEmpty();
                        if ($hasValues) {
                            Notification::make()
                                ->title('Cannot Delete Attributes')
                                ->body('Some attributes have associated values. Please delete them first.')
                                ->warning()
                                ->send();
                            $action->halt();
                        }
                    }),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }

    public static function getRelations(): array
    {
        return [
            ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'view' => Pages\ViewAttribute::route('/{record}'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
