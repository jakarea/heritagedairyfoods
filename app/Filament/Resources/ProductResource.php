<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Product;
use App\Models\ProductImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Support\Str;
use Filament\Tables\Actions\ActionGroup;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Products Management';
    protected static ?string $navigationBadgeTooltip = 'The number of products';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            })->columnSpanFull(),
                        Forms\Components\TextInput::make('subtitle')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('short_desc')
                            ->nullable()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'small' => 'Small',
                                'medium' => 'Medium',
                                'large' => 'Large',
                            ])
                            ->default('medium'),
                        Forms\Components\TextInput::make('weight')
                            ->nullable()
                            ->maxLength(255)
                            ->helperText('e.g., 100, 500, 1000, etc.(only grams)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('SEO Settings')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->nullable()
                            ->maxLength(65535),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('search_keywords')
                            ->nullable()
                            ->maxLength(65535),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing & Stock')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('offer_price')
                            ->nullable()
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Select::make('discount_in')
                            ->options([
                                'flat' => 'Flat',
                                'percentage' => 'Percentage',
                            ])
                            ->default('flat')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'draft' => 'Draft',
                                'out_of_stock' => 'Out of Stock',
                                'archived' => 'Archived',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->maxLength(255)->columnSpanFull(),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Categories & Tags')
                    ->schema([
                        Forms\Components\Select::make('categories')
                            ->multiple()
                            ->options(function () {
                                return Category::all()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->unique(Category::class, 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('slug', Str::slug($state));
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique(Category::class, 'slug'),
                                Forms\Components\Textarea::make('description')
                                    ->nullable(),
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('category-images')
                                    ->nullable(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $category = Category::create($data);
                                return $category->id;
                            })
                            ->required(),
                        Forms\Components\Select::make('tags')
                            ->multiple()
                            ->options(function () {
                                return Tag::all()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->unique(Tag::class, 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('slug', Str::slug($state));
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique(Tag::class, 'slug'),
                                Forms\Components\Textarea::make('description')
                                    ->nullable(),
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('tag-images')
                                    ->nullable(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $tag = Tag::create($data);
                                return $tag->id; // Fixed to return id instead of name
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('product-images')
                            ->preserveFilenames(),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Video')
                    ->schema([
                        Forms\Components\TextInput::make('video.title')
                            ->label('Video Title')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('video.sub_title')
                            ->label('Video Subtitle')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('video.url')
                            ->label('Video URL')
                            ->nullable()
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('video.description')
                            ->label('Video Description')
                            ->nullable()
                            ->maxLength(65535),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('product-detail-images')
                                    ->nullable()
                                    ->label('Detail Image')
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('blocks')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Block Title')
                                            ->nullable()
                                            ->maxLength(255),
                                        Forms\Components\Repeater::make('lists')
                                            ->schema([
                                                Forms\Components\TextInput::make('item')
                                                    ->label('List Item')
                                                    ->required()
                                                    ->maxLength(65535),
                                            ])
                                            ->label('List Items')
                                            ->required(),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? 'Block')
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['blocks'][0]['title'] ?? 'Detail Section')
                            ->columns(2),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Conclusion')
                    ->schema([
                        Forms\Components\TextInput::make('conclusion.title')
                            ->label('Conclusion Title')
                            ->nullable()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('conclusion.description')
                            ->label('Conclusion Description')
                            ->nullable()
                            ->maxLength(65535),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image') 
                ->extraImgAttributes(['class' => 'w-12 h-12 object-cover rounded-md'])->defaultImageUrl(url('images/image-not-found-2.jpg')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                ->suffix(' gm') 
                    ->sortable(),
                Tables\Columns\TextColumn::make('offer_price')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'active' => 'success',
                            'draft' => 'gray',
                            'out_of_stock' => 'danger',
                            'archived' => 'info',
                            default => 'secondary',
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories_count')
                    ->label('Categories')
                    ->getStateUsing(function ($record) {
                        return count($record->categories ?? []);
                    }),
                Tables\Columns\TextColumn::make('tags_count')
                    ->label('Tags')
                    ->getStateUsing(function ($record) {
                        return count($record->tags ?? []);
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'draft' => 'Draft',
                        'out_of_stock' => 'Out of Stock',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('categories')
                    ->options(function () {
                        return Category::all()->pluck('name', 'id');
                    })
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->whereJsonContains('categories', $data['value']);
                        }
                    })
                    ->multiple(),
                Tables\Filters\SelectFilter::make('tags')
                    ->options(function () {
                        return Tag::all()->pluck('name', 'id');
                    })
                    ->query(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->whereJsonContains('tags', $data['value']);
                        }
                    })
                    ->multiple(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('success'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ]) 
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Basic Information')
                    ->schema([
                        Components\TextEntry::make('name'),
                        Components\TextEntry::make('subtitle'),
                        Components\TextEntry::make('slug'),
                        Components\TextEntry::make('description')->html(),
                        Components\TextEntry::make('short_desc'),
                        Components\TextEntry::make('type'),
                        Components\TextEntry::make('weight'),
                    ])
                    ->columns(2),

                Components\Section::make('SEO Settings')
                    ->schema([
                        Components\TextEntry::make('meta_title'),
                        Components\TextEntry::make('meta_description'),
                        Components\TextEntry::make('meta_keywords'),
                        Components\TextEntry::make('search_keywords'),
                    ])
                    ->columns(2),

                Components\Section::make('Pricing & Stock')
                    ->schema([
                        Components\TextEntry::make('price')->money('bdt'),
                        Components\TextEntry::make('offer_price')->money('bdt'),
                        Components\TextEntry::make('discount_in'),
                        Components\TextEntry::make('sku'),
                        Components\TextEntry::make('status')->badge(),
                    ])
                    ->columns(2),

                Components\Section::make('Categories & Tags')
                    ->schema([
                        Components\TextEntry::make('categories')
                            ->label('Categories')
                            ->formatStateUsing(function ($record) {
                                $names = $record->category_records->pluck('name')->join(', ');
                                return $names ?: 'None';
                            }),
                        Components\TextEntry::make('tags')
                            ->label('Tags')
                            ->formatStateUsing(function ($record) {
                                $names = $record->tag_records->pluck('name')->join(', ');
                                return $names ?: 'None';
                            }),
                    ])
                    ->columns(2),

                Components\Section::make('Image')
                    ->schema([
                        Components\ImageEntry::make('image')
                            ->disk('public')
                            ->width(500)
                            ->height(350)
                            ->label('Product Image')
                    ])
                    ->collapsible(),

                Components\Section::make('Video')
                    ->schema([
                        Components\TextEntry::make('video.title')->label('Video Title'),
                        Components\TextEntry::make('video.sub_title')->label('Video Subtitle'),
                        Components\TextEntry::make('video.url')
                            ->label('Video URL')
                            ->formatStateUsing(fn($state) => $state ? "<a href='$state' target='_blank'>View Video</a>" : 'None')
                            ->html(),
                        Components\TextEntry::make('video.description')->label('Video Description'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Components\Section::make('Details')
                    ->schema([
                        Components\RepeatableEntry::make('details')
                            ->schema([
                                Components\ImageEntry::make('image')
                                    ->label('Detail Image')
                                    ->disk('public')
                                    ->width(400)
                                    ->height(350)
                                    ->extraImgAttributes(['alt' => 'Detail Image', 'class' => 'object-cover'])
                                    ->hidden(fn($state) => empty($state)),
                                Components\TextEntry::make('image')
                                    ->label('Detail Image')
                                    ->default('No image uploaded')
                                    ->hidden(fn($state) => !empty($state)),
                                Components\RepeatableEntry::make('blocks')
                                    ->schema([
                                        Components\TextEntry::make('title')
                                            ->label('Block Title')
                                            ->columnSpanFull(),

                                        Components\RepeatableEntry::make('lists')
                                            ->label('List Items')
                                            ->schema([
                                                Components\TextEntry::make('item')
                                                    ->label(' ')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->label('Blocks'),

                            ])

                            ->label('Product Details'),
                    ])
                    ->collapsible(),

                Components\Section::make('Conclusion')
                    ->schema([
                        Components\TextEntry::make('conclusion.title')->label('Conclusion Title'),
                        Components\TextEntry::make('conclusion.description')->label('Conclusion Description'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ImagesRelationManager::class,
            RelationManagers\BundlesRelationManager::class,
            RelationManagers\VariationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
