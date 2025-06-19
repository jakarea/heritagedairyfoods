<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Infolists;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter; 
use App\Filament\Resources\CategoryResource\Pages;
use Filament\Tables\Columns\{BadgeColumn, TextColumn, ImageColumn};
use Filament\Tables\Actions\{EditAction, DeleteAction, ViewAction, ForceDeleteAction, RestoreAction};
use Filament\Forms\Components\{TextInput, Select, Textarea, FileUpload, Toggle, Section};

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationBadgeTooltip = 'The number of category';
    protected static ?string $navigationGroup = 'Products Management';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Category Details')->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug($state)))
                        ->maxLength(100)
                        ->columnSpanFull()
                        ->unique(Category::class, 'name', ignoreRecord: true),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(100)
                        ->unique(Category::class, 'slug', ignoreRecord: true)
                        ->disabled(fn($record) => $record !== null)
                        ->dehydrated(),
                    Select::make('parent_id')
                        ->label('Parent Category')
                        ->relationship('parent', 'name')
                        ->nullable()
                        ->searchable()
                        ->options(function () {
                            return Category::pluck('name', 'id');
                        }),
                    Textarea::make('description')
                        ->nullable()
                        ->maxLength(65535),

                    FileUpload::make('image')
                        ->label('Category Image')
                        ->nullable()
                        ->image()
                        ->directory('products/category-images')
                        ->preserveFilenames()
                        ->disk('public')
                        ->visibility('public'),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->live()
                        ->dehydrateStateUsing(fn($state) => (bool) $state)
                        ->default(true),

                ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Category Details')
                    ->icon('heroicon-o-tag')
                    ->headerActions([
                        Infolists\Components\Actions\Action::make('edit')
                            ->label('Edit Category')
                            ->icon('heroicon-o-pencil')
                            ->url(fn($record) => static::getUrl('edit', ['record' => $record]))
                            ->color('primary'),
                    ])
                    ->schema([
                        Infolists\Components\Section::make([

                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Category Name')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('slug')
                                    ->label('Slug')
                                    ->icon('heroicon-o-link')
                                    ->copyable()
                                    ->copyMessage('Slug copied to clipboard!')
                                    ->badge()
                                    ->color('gray')
                                    ->extraAttributes(['class' => 'mt-2']),
                                Infolists\Components\TextEntry::make('number_of_products')
                                    ->label('Number of Products')
                                    ->icon('heroicon-o-queue-list')
                                    ->suffix(' products')
                                    ->numeric()
                                    ->formatStateUsing(fn($state) => number_format($state))
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('is_active')
                                    ->badge()
                                    ->label('Status')
                                    ->color(fn(string $state): string => match ($state) {
                                        '1' => 'success',
                                        default => 'danger',
                                    })->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive'),
                            ])->columnSpan(7),

                            Infolists\Components\Group::make([
                                Infolists\Components\ImageEntry::make('image')
                                    ->label('Category Image')
                                    ->disk('public')
                                    ->height(220)
                                    ->width(220)
                                    ->square()
                                    ->defaultImageUrl(url('images/inf-icon.png'))
                                    ->extraImgAttributes(['class' => 'ring-4 ring-primary-200 shadow-lg']),
                            ])->columnSpan(5),

                        ])->columns(12),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull()
                            ->markdown()
                            ->placeholder('No description provided')
                            ->extraAttributes(['class' => 'prose prose-sm max-w-none mt-4']),
                    ])
                    ->columns(3)
                    ->extraAttributes(['class' => 'bg-white rounded-xl shadow-sm']),
                Infolists\Components\Split::make([
                    Infolists\Components\Section::make('Parent Category')
                        ->icon('heroicon-o-arrow-up-on-square')
                        ->collapsible()
                        ->schema([
                            Infolists\Components\TextEntry::make('parent_count')
                                ->label('Total Parent Categories')
                                ->state(fn($record) => $record->parent ? 1 : 0)
                                ->badge()
                                ->color('info')
                                ->suffix(' parent'),
                            Infolists\Components\TextEntry::make('parent.name')
                                ->label('Parent Category Name')
                                ->placeholder('No parent category')
                                ->markdown()
                                ->extraAttributes(['class' => 'prose prose-sm max-w-none']),
                        ])
                        ->extraAttributes(['class' => 'bg-white rounded-xl shadow-sm']),
                    Infolists\Components\Section::make('Child Categories')
                        ->icon('heroicon-o-arrow-down-on-square')
                        ->collapsible()
                        ->schema([
                            Infolists\Components\TextEntry::make('children_count')
                                ->label('Total Child Categories')
                                ->state(fn($record) => $record->children->count())
                                ->badge()
                                ->color('success')
                                ->suffix(' children'),
                            Infolists\Components\TextEntry::make('children')
                                ->label('Child Category Names')
                                ->state(fn($record) => $record->children->pluck('name')->join(', ') ?: 'None')
                                ->placeholder('No child categories')
                                ->formatStateUsing(fn($state) => $state === 'None' ? $state : collect(explode(', ', $state))->map(fn($name) => "- $name")->join("\n"))
                                ->markdown()
                                ->extraAttributes(['class' => 'prose prose-sm max-w-none']),
                        ])
                        ->extraAttributes(['class' => 'bg-white rounded-xl shadow-sm']),
                ])->from('lg')->columns(2)->grow(false),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->extraImgAttributes(['class' => 'w-12 h-12 object-cover rounded-md'])
                    ->defaultImageUrl(url('images/inf-icon.png')),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('slug')->sortable(),
                TextColumn::make('parent.name')->label('Parent Category')->sortable(),
                TextColumn::make('children_count')
                    ->label('Total Child Categories')
                    ->state(fn($record) => $record->children()->count())
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('number_of_products')->badge()->color('info'),
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive'),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->label('Filter by Parent Category'),
                SelectFilter::make('is_active')
                    ->label('Filter by Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()->color('success'),
                    EditAction::make(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Category')
                        ->modalDescription('Are you sure you want to delete this Category? It will be moved to the trash.')
                        ->modalButton('Confirm')
                        ->before(function ($record, $action) {
                            if ($record->children()->exists()) {
                                Notification::make()
                                    ->title('Cannot Delete')
                                    ->body('This Category has associated children. Please delete them first.')
                                    ->warning()
                                    ->send();
                                $action->halt();
                            }
                        })
                        ->action(fn($record) => $record->delete())
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Category Deleted')
                                ->body('The Category has been moved to the trash.')
                        ),
                    RestoreAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Restore Category')
                        ->modalDescription('Are you sure you want to restore this Category?')
                        ->modalButton('Confirm')
                        ->visible(fn($record) => $record->trashed())
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Category Restored')
                                ->body('The Category has been restored.')
                        ),
                    ForceDeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Category')
                        ->modalDescription('Are you sure you want to permanently delete this Category? This action cannot be undone.')
                        ->modalButton('Confirm')
                        ->visible(fn($record) => $record->trashed())
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('category Permanently Deleted')
                                ->body('The category has been permanently deleted.')
                        ),
                ])

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($records, $action) {
                        $hasChildren = $records->filter(fn($record) => $record->children()->exists())->isNotEmpty();

                        if ($hasChildren) {
                            Notification::make()
                                ->title('Cannot Delete Categories')
                                ->body('Some categories have subcategories. Please delete them first.')
                                ->warning()
                                ->send();

                            $action->halt(); // Stop the bulk delete action
                        }
                    }),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
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
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
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
