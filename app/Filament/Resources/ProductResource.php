<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use Filament\Forms\Components\{TextInput, Select, Textarea, FileUpload, Grid, Toggle, Repeater, RichEditor, Section};
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
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            })->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->nullable()
                            ->label('Sub Title')
                            ->maxLength(255),
                        Textarea::make('short_desc')
                            ->nullable()
                            ->label('Short Description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        RichEditor::make('description')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(9)
                    ->columns(2),

                Section::make('Sidebar Information')
                    ->schema([
                        Select::make('categories')
                            ->multiple()
                            ->options(function () {
                                return Category::all()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->unique(Category::class, 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(Category::class, 'slug'),
                                Textarea::make('description')
                                    ->nullable(),
                                FileUpload::make('image')
                                    ->image()
                                    ->directory('category-images')
                                    ->nullable(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $category = Category::create($data);
                                return $category->id;
                            })
                            ->required(),
                        Select::make('tags')
                            ->multiple()
                            ->options(function () {
                                return Tag::all()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->unique(Tag::class, 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(Tag::class, 'slug'),
                                Textarea::make('description')
                                    ->nullable(),
                                FileUpload::make('image')
                                    ->image()
                                    ->directory('tag-images')
                                    ->nullable(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $tag = Tag::create($data);
                                return $tag->id;
                            }),

                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'draft' => 'Draft',
                                'archived' => 'Archived',
                                'out_of_stock' => 'Out of Stock',
                            ])
                            ->searchable(),
                       
                        TextInput::make('video')
                            ->label('Video URL')
                            ->nullable(),
                            Select::make('type')
                            ->options([
                                'simple' => 'Simple',
                                'variable' => 'Variable',
                                'bundle' => 'Bundle',
                            ])
                            ->searchable(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columnSpan(3),

                Section::make('Pricing & Stock')
                    ->schema([
                        TextInput::make('base_price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->prefix('BDT'),
                        TextInput::make('discount_price')
                            ->nullable()
                            ->numeric()
                            ->prefix('BDT'),
                        Select::make('discount_in')
                            ->options([
                                'flat' => 'Flat',
                                'percentage' => 'Percentage',
                            ])
                            ->default('flat')
                            ->required(),
                        TextInput::make('weight')
                            ->nullable()
                            ->helperText('In grams only')
                            ->maxLength(255),
                        TextInput::make('sku')
                            ->required()
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->maxLength(255)->columnSpan(2),

                    ])
                    ->columns(3),

                Section::make('Media')
                    ->schema([
                        Section::make('Featured Image')
                            ->label('Featured Image')
                            ->schema([
                                FileUpload::make('image')
                                    ->image()
                                    ->directory('product/images')
                                    ->preserveFilenames(),
                            ])->columnSpan(1),
                        Section::make('Gallery Images')
                            ->label('Gallery Images')
                            ->schema([
                                FileUpload::make('images')
                                    ->image()
                                    ->multiple()
                                    ->directory('product/images')
                                    ->preserveFilenames(),
                            ])->columnSpan(1)
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make('Product Variations')
                    ->schema([

                        Repeater::make('variations')
                        ->label(' ')
                            ->relationship('variations')
                            ->schema([

                                Select::make('attribute_values')
                                    ->multiple()
                                    ->options(function () {
                                        return ProductAttributeValue::where('is_active', true)
                                            ->with('productAttribute')
                                            ->get()
                                            ->mapWithKeys(fn($value) => [
                                                $value->id => "{$value->productAttribute->name}: {$value->value}",
                                            ]);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->label('Variation Attributes'),
                                TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->required(),
                                TextInput::make('discount_price')
                                    ->label('Discount Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->nullable(),
                                TextInput::make('discount_in')
                                    ->label('Discount In')
                                    ->maxLength(50)
                                    ->nullable(),
                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                                TextInput::make('sku')
                                    ->label('Variation SKU')
                                    ->maxLength(50)
                                    ->nullable()
                                    ->unique('product_variations', 'sku', ignoreRecord: true),
                                TextInput::make('weight')
                                    ->label('Weight')
                                    ->numeric()
                                    ->suffix('kg')
                                    ->minValue(0)
                                    ->nullable(),
                                Toggle::make('is_default')
                                    ->label('Default Variation')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(function (array $state) {
                                $attributes = collect($state['attribute_values'] ?? [])
                                    ->map(fn($id) => ProductAttributeValue::find($id)?->value)
                                    ->filter()
                                    ->implode(', ');
                                return $attributes ?: 'Variation';
                            })
                            ->addActionLabel('Add Variation')
                            ->columnSpan(9),

                        FileUpload::make('images')
                            ->image() 
                            ->directory('product/images')
                            ->preserveFilenames()
                            ->columnSpan(3),

                    ])->columnSpan(12)->columns(12),

                Section::make('SEO Settings')
                    ->schema([
                        TextInput::make('meta_title')
                            ->nullable()
                            ->columnSpanFull()
                            ->maxLength(255),
                        TextInput::make('meta_keywords')
                            ->nullable()
                            ->maxLength(255),
                        TextInput::make('search_keywords')
                            ->nullable()
                            ->maxLength(65535),
                        Textarea::make('meta_description')
                            ->nullable()
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ])
                    ->columns(2),

                // Forms\Components\Section::make('Variations')
                //     ->schema([
                //         Repeater::make('attributeValues')
                //             ->relationship()
                //             ->schema([
                //                 Select::make('product_attribute_value_id')
                //                     ->label('Attribute Value')
                //                     ->options(function () {
                //                         return ProductAttributeValue::where('is_active', true)
                //                             ->with('productAttribute')
                //                             ->get()
                //                             ->mapWithKeys(fn($value) => [
                //                                 $value->id => "{$value->productAttribute->name}: {$value->value}",
                //                             ]);
                //                     })
                //                     ->required()
                //                     ->searchable(),
                //                 TextInput::make('price_adjustment')
                //                     ->label('Price Adjustment')
                //                     ->numeric()
                //                     ->prefix('$')
                //                     ->nullable(),
                //                 TextInput::make('sku')
                //                     ->label('Variation SKU')
                //                     ->maxLength(50)
                //                     ->nullable(),
                //                     // ->unique('product_attribute_product', 'sku', ignoreRecord: true),
                //             ])
                //             ->columns(3)
                //             ->label('Attribute Values')
                //             ->addActionLabel('Add Variation'),
                //     ])
                //     ->collapsible(),
            ])->columns(12);
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
