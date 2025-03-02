<?php

namespace App\Filament\Resources;

use App\Models\Category;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Filament\Forms\Components\{TextInput, Select};
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\CategoryResource\Pages;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationGroup = 'Products Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                    ->maxLength(55),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Category::class, 'slug')
                    ->disabled(fn ($record) => $record !== null)
                    ->dehydrated(),

                Select::make('parent_id')
                    ->label('Parent Category')
                    ->relationship('parent', 'name')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('slug')->sortable(),
                TextColumn::make('parent.name')->label('Parent Category')->sortable(),
                BadgeColumn::make('created_at')->date(),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->label('Filter by Parent Category'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Category')
                    ->modalDescription(fn ($record) => 
                        $record->children()->exists()
                            ? 'This category has subcategories. Please delete them first before proceeding.'
                            : 'Are you sure you want to delete this category?'
                    )
                    ->modalButton('Got it')
                    ->before(function ($record, $action) {
                        if ($record->children()->exists()) {
                            Notification::make()
                                ->title('Cannot Delete')
                                ->body('This category has subcategories. Please delete them first.')
                                ->warning()
                                ->send();
                            
                            $action->halt(); // Stop the action properly
                        }
                    })
                    ->action(fn ($record) => $record->delete()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                ->before(function ($records, $action) {
                    $hasChildren = $records->filter(fn ($record) => $record->children()->exists())->isNotEmpty();

                    if ($hasChildren) {
                        Notification::make()
                            ->title('Cannot Delete Categories')
                            ->body('Some categories have subcategories. Please delete them first.')
                            ->warning()
                            ->send();

                        $action->halt(); // Stop the bulk delete action
                    }
                })
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
