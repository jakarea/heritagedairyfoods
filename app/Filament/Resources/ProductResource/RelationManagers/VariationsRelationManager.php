<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\ProductVariationAttribute;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\{TextInput, Select, Textarea, FileUpload, Grid, Toggle, Repeater, RichEditor, Section, TagsInput};

class VariationsRelationManager extends RelationManager
{
    protected static string $relationship = 'variations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->disabled(fn($record) => $record !== null)
                    ->columnSpanFull()
                    ->hidden(fn($operation) => $operation === 'create'),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->hidden(fn($operation) => $operation === 'create'),
                Forms\Components\TextInput::make('discount_price')
                    ->numeric()
                    ->prefix('$')
                    ->nullable()
                    ->hidden(fn($operation) => $operation === 'create'),
                Forms\Components\Select::make('discount_in')
                    ->options([
                        'flat' => 'Flat',
                        'percentage' => 'Percentage',
                    ])
                    ->default('flat')
                    ->required()
                    ->hidden(fn($operation) => $operation === 'create'),
                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->default(0)
                    ->nullable()
                    ->hidden(fn($operation) => $operation === 'create'),
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->hidden(fn($operation) => $operation === 'create'),
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(\App\Models\ProductVariation::class, 'sku', ignoreRecord: true)
                    ->hidden(fn($operation) => $operation === 'create'),

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
                                            )->columnSpan(2),
                                    ])
                                    ->columns(3)
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
                    ->columnSpanFull()
                    ->hidden(fn($operation) => $operation === 'edit'),

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
                    ])
                    ->hidden(fn($operation) => $operation === 'edit'),

                Toggle::make('is_default')
                    ->default(false)
                    ->hidden(fn($operation) => $operation === 'create'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image.image_path')->disk('public')->width(80)->height(50)->defaultImageUrl(url('images/inf-icon.png')),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('sku'),
                Tables\Columns\TextColumn::make('price')
                    ->money('bdt'),
                Tables\Columns\TextColumn::make('discount_price')
                    ->money('bdt'),
                Tables\Columns\TextColumn::make('discount_in')
                    ->money('bdt'),
                Tables\Columns\TextColumn::make('weight'),
                Tables\Columns\TextColumn::make('stock'),

                Tables\Columns\TextColumn::make('directAttributes')
                    ->label('Attributes')
                    ->formatStateUsing(function ($record) {
                        $attributes = $record->directAttributes;
                        $formattedAttributes = $attributes->map(function ($attribute) {
                            $attributeName = $attribute->name;
                            $selectedValue = $attribute->values->firstWhere('id', $attribute->pivot->product_attribute_value_id);
                            $attributeValue = $selectedValue ? $selectedValue->value : 'N/A';
                            return "{$attributeName}: {$attributeValue}";
                        })->implode(', ');

                        return $formattedAttributes ?: 'No attributes';
                    })
                    ->sortable(false)
                    ->searchable(false),
                Tables\Columns\BooleanColumn::make('is_default')
                    ->label('Default Variation'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    // ->beforeFormFilled(function (array $data) {
                         
                    // })
                    ->after(function (array $data) {
                        // just clean/prepare $data   
                        $product = $this->getOwnerRecord();
                        $createdVariations = [];

                        if (!empty($data['product_variations'])) { 
                            foreach ($data['product_variations'] as $index => $variationData) { 
                                // Save variation
                                $variation = ProductVariation::create([
                                    'product_id' => $product->id,
                                    'name' => $variationData['name'],
                                    'price' => $variationData['price'],
                                    'discount_price' => $variationData['discount_price'],
                                    'discount_in' => $variationData['discount_in'],
                                    'stock' => $variationData['stock'],
                                    'sku' => $variationData['sku'],
                                    'weight' => $variationData['weight'],
                                    'is_default' => 0,
                                ]);

                                $createdVariations[] = $variation;

                                // Save attributes
                                if (!empty($data['product_attributes'])) {
                                    $variationAttributes = [];

                                    foreach ($data['product_attributes'] as $attributeSet) {
                                        if (!empty($attributeSet['product_attribute_values'])) {
                                            $value = $attributeSet['product_attribute_values'][0];

                                            $variationAttributes[] = [
                                                'product_variation_id' => $variation->id,
                                                'product_attribute_id' => $attributeSet['product_attribute_id'],
                                                'product_attribute_value_id' => $value,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ];
                                        }
                                    }

                                    if (!empty($variationAttributes)) {
                                        ProductVariationAttribute::insert($variationAttributes);
                                    }
                                }

                                // Save image
                                if (!empty($variationData['image'])) {
                                    ProductImage::create([
                                        'product_id' => $product->id,
                                        'variation_id' => $variation->id,
                                        'image_path' => $variationData['image'],
                                        'is_primary' => false,
                                    ]);
                                }
                            }
                        }
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    // Tables\Actions\ViewAction::make()->color('success'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function eagerLoad(): array
    {
        return ['attributes.attribute', 'attributes.attributeValue'];
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
