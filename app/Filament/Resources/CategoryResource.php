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
use Filament\Forms\Components\{TextInput, Select, Textarea, FileUpload};
use Filament\Tables\Columns\{TextColumn, ImageColumn};
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\CategoryResource\Pages;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Section;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationBadgeTooltip = 'The number of category';
    protected static ?string $navigationGroup = 'Products Management';
    protected static ?int $navigationSort = 2;

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
                        ->unique(Category::class, 'name', ignoreRecord: true),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(100)
                        ->unique(Category::class, 'slug', ignoreRecord: true)
                        ->disabled(fn($record) => $record !== null)
                        ->dehydrated(),
                    Textarea::make('description')
                        ->nullable()
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Select::make('parent_id')
                        ->label('Parent Category')
                        ->relationship('parent', 'name')
                        ->nullable()
                        ->searchable()
                        ->options(function () {
                            // return Category::whereNull('parent_id')->pluck('name', 'id');
                            return Category::pluck('name', 'id');
                        }),

                    Select::make('is_active')
                        ->label('Status')
                        ->options([
                            true => 'Active',
                            false => 'Inactive',
                        ])
                        ->default(true)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->native(false),

                    FileUpload::make('image')
                        ->label('Category Image')
                        ->nullable()
                        ->image()
                        ->directory('products/categories')
                        ->preserveFilenames()
                        ->disk('public')
                        ->visibility('public')
                        ->columnSpanFull(),
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
                                Infolists\Components\TextEntry::make('number_of_products')
                                    ->label('Number of Products')
                                    ->icon('heroicon-o-queue-list')
                                    ->suffix(' products')
                                    ->numeric()
                                    ->formatStateUsing(fn($state) => number_format($state))
                                    ->badge()
                                    ->color('success')
                                    ->extraAttributes(['class' => 'mt-2']),
                            ])->columnSpan(7),

                            Infolists\Components\Group::make([
                                Infolists\Components\ImageEntry::make('image')
                                    ->label('Category Image')
                                    ->disk('public')
                                    ->height(220)
                                    ->width(220)
                                    ->square()
                                    ->defaultImageUrl(url('images/image-not-found-2.jpg'))
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
                    ->defaultImageUrl(url('images/image-not-found-2.jpg')),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('slug')->sortable(),
                TextColumn::make('parent.name')->label('Parent Category')->sortable(),
                TextColumn::make('children_count')
                    ->label('Total Child Categories')
                    ->state(fn($record) => $record->children()->count())
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('number_of_products')->sortable()->badge()
                    ->color('info')->suffix(' products'),
                TextColumn::make('is_active')
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
                    Tables\Actions\ViewAction::make()->color('success'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Category')
                        ->modalDescription(
                            fn($record) =>
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

                                $action->halt();
                            }
                        })
                        ->action(fn($record) => $record->delete()),
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
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
