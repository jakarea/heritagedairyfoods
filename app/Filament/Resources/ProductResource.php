<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\ProductVariationAttribute;
use Filament\Forms\Components\{Component, TextInput, Select, Textarea, FileUpload, Grid, Toggle, Repeater, RichEditor, Section, Hidden, Radio, TagsInput};
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Infolists\Components\Grid as ComponentsGrid;
use Illuminate\Support\Str;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Products Management';
    protected static ?string $navigationBadgeTooltip = 'The number of products';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 3;

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
                                return Category::where('is_active', true)->pluck('name', 'id');
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
                                    ->directory('products/category-images')
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
                                return Tag::where('is_active', true)->pluck('name', 'id');
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
                                    ->directory('products/tag-images')
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
                            ->default('active')
                            ->nullable()
                            ->searchable(),

                        TextInput::make('video_url')
                            ->label('Video URL')
                            ->nullable(),
                        Select::make('type')
                            ->options([
                                'simple' => 'Simple',
                                'variable' => 'Variable',
                                'bundle' => 'Bundle',
                            ])
                            ->default('simple')
                            ->nullable()
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
                            ->default('flat'),
                        TextInput::make('stock')
                            ->nullable()
                            ->numeric()
                            ->maxLength(255),
                        TextInput::make('sku')
                            ->nullable()
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->maxLength(50),

                    ])
                    ->columns(3),

                Section::make('Media')
                    ->schema([
                        Section::make('Featured Image')
                            ->label('Featured Image') 
                            ->schema([
                                FileUpload::make('featured_image')
                                    ->image()
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('products/featured-images')
                                    ->preserveFilenames()

                            ])->columnSpan(1),
                        Section::make('Gallery Images')
                            ->label('Gallery Images')
                            ->schema([
                                FileUpload::make('gallery_images')
                                    ->multiple()
                                    ->image()
                                    ->disk('public')
                                    ->directory('products/gallery-images')
                                    ->preserveFilenames(),
                            ])->columnSpan(1)
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make('Product Attributes')
                    ->schema([
                        Repeater::make('product_attributes')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Select::make('product_attribute_id')
                                            ->label('Attribute')
                                            ->searchable()
                                            ->options(function () {
                                                return ProductAttribute::pluck('name', 'id')->toArray();
                                            })
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $allAttributes = $get('../../attributes') ?? [];
                                                $selectedAttributes = collect($allAttributes)
                                                    ->pluck('product_attribute_id')
                                                    ->filter()
                                                    ->toArray();

                                                $currentId = $state;
                                                $count = array_count_values($selectedAttributes)[$currentId] ?? 0;

                                                if ($currentId && $count > 1) {
                                                    $set('product_attribute_id', null);
                                                    Notification::make()
                                                        ->title('This attribute is already selected')
                                                        ->warning()
                                                        ->send();
                                                }

                                                // Reset values when attribute changes
                                                $set('product_attribute_values', []);
                                            })
                                            ->suffixAction(
                                                Action::make('create_attribute')
                                                    ->icon('heroicon-o-plus')
                                                    ->form([
                                                        TextInput::make('name')
                                                            ->label('Attribute Name')
                                                            ->required()
                                                            ->unique(ProductAttribute::class, 'name'),
                                                    ])
                                                    ->action(function (array $data, callable $set) {
                                                        $baseSlug = Str::slug($data['name']);
                                                        $slug = $baseSlug;
                                                        $counter = 1;
                                                        while (ProductAttribute::where('slug', $slug)->exists()) {
                                                            $slug = "{$baseSlug}-{$counter}";
                                                            $counter++;
                                                        }

                                                        $newAttribute = ProductAttribute::create([
                                                            'name' => $data['name'],
                                                            'slug' => $slug,
                                                        ]);

                                                        $set('product_attribute_id', $newAttribute->id);

                                                        Notification::make()
                                                            ->title('Attribute created')
                                                            ->success()
                                                            ->send();
                                                    })
                                                    ->modalHeading('Create New Attribute')
                                                    ->modalSubmitActionLabel('Create')
                                            ),
                                        Select::make('product_attribute_values')
                                            ->label('Attribute Values')
                                            ->multiple() // Allow multiple selections
                                            ->options(function (callable $get) {
                                                $attributeId = $get('product_attribute_id');
                                                if ($attributeId) {
                                                    return ProductAttributeValue::where('product_attribute_id', $attributeId)
                                                        ->pluck('value', 'id')
                                                        ->toArray();
                                                }
                                                return [];
                                            })
                                            ->visible(function (callable $get) {
                                                return !empty($get('product_attribute_id'));
                                            })
                                            ->live(debounce: 500)
                                            ->columnSpan(2)
                                            ->suffixAction(
                                                Action::make('add_attribute_value')
                                                    ->icon('heroicon-o-plus')
                                                    ->form([
                                                        TextInput::make('value')
                                                            ->label('New Attribute Value')
                                                            ->required(),
                                                    ])
                                                    ->action(function (array $data, callable $get, callable $set) {
                                                        $attributeId = $get('product_attribute_id');
                                                        if (!$attributeId) {
                                                            Notification::make()
                                                                ->title('Please select an attribute first')
                                                                ->warning()
                                                                ->send();
                                                            return;
                                                        }

                                                        $baseSlug = Str::slug($data['value']);
                                                        $slug = $baseSlug;
                                                        $counter = 1;
                                                        while (ProductAttributeValue::where('slug', $slug)->exists()) {
                                                            $slug = "{$baseSlug}-{$counter}";
                                                            $counter++;
                                                        }

                                                        $newValue = ProductAttributeValue::create([
                                                            'product_attribute_id' => $attributeId,
                                                            'value' => $data['value'],
                                                            'slug' => $slug,
                                                        ]);

                                                        $currentValues = $get('product_attribute_values') ?? [];
                                                        $currentValues[] = (string) $newValue->id; // Ensure string for Select
                                                        $set('product_attribute_values', $currentValues);

                                                        Notification::make()
                                                            ->title('Attribute value added')
                                                            ->success()
                                                            ->send();
                                                    })
                                                    ->modalHeading('Add New Attribute Value')
                                                    ->modalSubmitActionLabel('Add')
                                                    ->visible(function (callable $get) {
                                                        return !empty($get('product_attribute_id'));
                                                    })
                                                    ->disabled(function (callable $get) {
                                                        return empty($get('product_attribute_id'));
                                                    })
                                            ),
                                    ])
                                    ->columns(4)
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Add Another Attribute') 
                            ->maxItems(3) 
                            ->addable(function (callable $get) {
                                $attributes = $get('product_attributes') ?? [];

                                // Filter out incomplete ones
                                $validAttributes = collect($attributes)->filter(function ($attr) {
                                    return !empty($attr['product_attribute_id']) &&
                                        !empty($attr['product_attribute_values']) &&
                                        count($attr['product_attribute_values']) > 0;
                                });

                                return $validAttributes->count() >= 1;
                            })
                            ->columnSpanFull(),

                        Actions::make([
                            Action::make('generate_variants')
                                ->label('Generate Variants')
                                ->visible(function (callable $get) {
                                    $attributes = $get('product_attributes') ?? [];

                                    // Filter out incomplete ones
                                    $validAttributes = collect($attributes)->filter(function ($attr) {
                                        return !empty($attr['product_attribute_id']) &&
                                            !empty($attr['product_attribute_values']) &&
                                            count($attr['product_attribute_values']) > 0;
                                    });

                                    return $validAttributes->count() >= 2;
                                })
                                ->action(function (callable $get, callable $set) {
                                    $attributes = collect($get('product_attributes'))->filter(function ($attr) {
                                        return !empty($attr['product_attribute_id']) &&
                                            !empty($attr['product_attribute_values']);
                                    })->map(function ($attr) {
                                        $attribute = ProductAttribute::find($attr['product_attribute_id']);
                                        if (!$attribute) {
                                            return null;
                                        }

                                        return [
                                            'name' => $attribute->name,
                                            'values' => ProductAttributeValue::whereIn('id', $attr['product_attribute_values'])->pluck('value')->toArray(),
                                        ];
                                    })->filter()->toArray();

                                    $combinations = self::generateCombinations($attributes);

                                    $variantData = collect($combinations)->map(function ($combo) {
                                        $name = implode('-', array_values($combo));
                                        return [
                                            'name' => $name,
                                            'sku' => strtoupper(Str::random(8)),
                                            'price' => 0,
                                            'stock' => 0,
                                            'weight' => 0,
                                            'is_default' => false,
                                        ];
                                    })->toArray();

                                    $set('product_variations', $variantData);
                                }),

                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Product Variations')
                    ->schema([
                        Repeater::make('product_variations')
                            ->label('Generated Variants')
                            ->schema([

                                Grid::make(6)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Variation Name')
                                            ->required(),
                                        TextInput::make('sku')->required(),
                                        TextInput::make('price')->numeric()->required(),
                                        TextInput::make('discount_price')->numeric(),
                                        Select::make('discount_in')
                                            ->options([
                                                'flat' => 'Flat',
                                                'percentage' => 'Percentage',
                                            ])
                                            ->required()
                                            ->default('flat'),
                                        TextInput::make('stock')->numeric()->required(),
                                        TextInput::make('weight')->numeric(),
                                    ])->columnSpan(4)->columns(4),


                                FileUpload::make('image')
                                    ->image()
                                    ->disk('public')
                                    ->label('Variation Image')
                                    ->directory('products/variation-images')
                                    ->imagePreviewHeight('100')->columnSpan(2),
                            ])
                            ->columns(6)
                            ->default([])
                            ->addable(false)
                            ->reorderable(false)
                            ->hidden(fn(callable $get) => count($get('product_attributes') ?? []) < 1)

                    ]),

                Section::make('SEO Settings')
                    ->schema([
                        TextInput::make('meta_title')
                            ->nullable()
                            ->columnSpanFull()
                            ->maxLength(255),
                        TextInput::make('meta_keywords')
                            ->nullable()
                            ->maxLength(255),
                        TagsInput::make('search_keywords')
                            ->separator(',')
                            ->reorderable()
                            ->suggestions([
                                'tailwindcss',
                                'alpinejs',
                                'laravel',
                                'livewire',
                            ])
                            ->splitKeys(['Tab', ' '])
                            ->nestedRecursiveRules([
                                'min:2',
                                'max:255',
                            ]),
                        Textarea::make('meta_description')
                            ->nullable()
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ])
                    ->columns(2),


            ])->columns(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Feat Image')
                    ->getStateUsing(function ($record) {
                        return $record->featuredImage?->image_path ?? null;
                    })
                    ->extraImgAttributes(['class' => 'w-12 h-12 object-cover rounded-md'])
                    ->defaultImageUrl(url('images/inf-icon.png')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->money('BDT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_price')
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

                Components\TextEntry::make('search_keywords')
                    ->label('Search Keywords')
                    ->getStateUsing(function ($record) {
                        $keywords = array_map('trim', explode(',', $record->search_keywords));
                        return collect($keywords);
                    })
                    ->badge()
                    ->copyable()
                    ->html(),

                Components\Section::make('Basic Information')
                    ->schema([
                        Components\TextEntry::make('name'),
                        Components\TextEntry::make('subtitle'),
                        Components\TextEntry::make('slug'),
                        Components\TextEntry::make('short_desc'),
                        Components\TextEntry::make('status')->badge()
                            ->color(function ($state) {
                                return match ($state) {
                                    'active' => 'success',
                                    'draft' => 'gray',
                                    'out_of_stock' => 'danger',
                                    'archived' => 'info',
                                    default => 'secondary',
                                };
                            }),
                        Components\TextEntry::make('type')->badge()
                            ->color(function ($state) {
                                return match ($state) {
                                    'simple' => 'success',
                                    'variable' => 'primary',
                                    'bundle' => 'info',
                                    default => 'secondary',
                                };
                            }),
                        Components\TextEntry::make('description')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Components\Section::make('Pricing & Stock')
                    ->schema([
                        Components\TextEntry::make('base_price')->money('bdt'),
                        Components\TextEntry::make('discount_price')->money('bdt'),
                        Components\TextEntry::make('discount_in'),
                        Components\TextEntry::make('sku'),

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

                Components\Section::make('SEO Settings')
                    ->schema([
                        Components\TextEntry::make('meta_title'),
                        Components\TextEntry::make('meta_keywords'),
                        Components\TextEntry::make('meta_description')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ImagesRelationManager::class,
            RelationManagers\VariationsRelationManager::class,
            RelationManagers\BundlesRelationManager::class,
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

    protected static function generateCombinations(array $attributes): array
    {
        $sets = array_map(function ($attribute) {
            return array_map(function ($value) use ($attribute) {
                return [$attribute['name'] => $value];
            }, $attribute['values']);
        }, $attributes);

        $combinations = [[]];

        foreach ($sets as $set) {
            $tmp = [];
            foreach ($combinations as $product) {
                foreach ($set as $item) {
                    $tmp[] = array_merge($product, $item);
                }
            }
            $combinations = $tmp;
        }

        return $combinations;
    }
}
