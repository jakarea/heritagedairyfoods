<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Actions\{EditAction, DeleteAction, ViewAction, ForceDeleteAction, RestoreAction};
use Filament\Forms\Components\{TextInput, DateTimePicker, Select, Textarea, FileUpload, Grid, Toggle, Repeater, RichEditor, Section, Hidden};

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Order Management';
    protected static ?string $navigationBadgeTooltip = 'The number of orders';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Hidden::make('order_by')
                ->default(Auth::id())
                ->required(),
            Hidden::make('total') 
                ->nullable(),
            Hidden::make('subtotal') 
                ->nullable(),
            TextInput::make('order_number')
                ->required()
                ->default(function () {
                    do {
                        $orderNumber = Str::upper(Str::random(8));
                    } while (Order::where('order_number', $orderNumber)->exists());
                    return $orderNumber;
                })
                ->required()->columnSpanFull(),
            Section::make('Customer Inforation')->schema([
                Select::make('customer_id')
                    ->label('Select Customer')
                    ->options(function () {
                        return Customer::pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $customer = Customer::find($state);
                        if ($customer) {
                            $set('email', $customer->email);
                            $set('phone', $customer->phone);
                            $set('shipping_address', implode(', ', array_filter([
                                $customer->street_address,
                                $customer->upazila,
                                $customer->district,
                                $customer->city,
                                $customer->zip_code,
                                $customer->country,
                            ])));
                        } else {
                            $set('email', null);
                            $set('phone', null);
                        }
                    })
                    ->createOptionForm([

                        Section::make('Customer Info')->schema([
                            TextInput::make('name')->required(),
                            TextInput::make('email')
                                ->required()
                                ->email()
                                ->unique(Customer::class, 'email'),
                            TextInput::make('phone')
                                ->label('Phone')
                                ->required(),
                            TextInput::make('password')
                                ->password()
                                ->required()
                                ->default('1234567890'),

                        ])->columns(2),
                    ])
                    ->createOptionUsing(function (array $data, Set $set) {
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => Hash::make($data['password']),
                        ]);

                        $customer = Customer::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'phone' => $data['phone'],
                            'user_id' => $user->id,
                        ]);

                        // Set the other fields after customer creation
                        $set('email', $customer->email);
                        $set('phone', $customer->phone);

                        return $customer->id;
                    }),
                TextInput::make('email')->label('Email')->disabled(),
                TextInput::make('phone')->label('Phone'),
            ])->columns(3),

            Section::make('Product Selection')->schema([
                Select::make('product_id')
                    ->label('Select Product')
                    ->placeholder('Choose a product')
                    ->options(function () {
                        return Product::pluck('name', 'id');
                    })
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $products = $get('selected_products') ?? [];
                                if (!collect($products)->contains('product_id', $product->id)) {
                                    $products[] = [
                                        'product_id' => $product->id,
                                        'name' => $product->name,
                                        'quantity' => 1,
                                        'price' => $product->discount_price ? $product->discount_price : $product->base_price,
                                    ];
                                    $set('selected_products', $products);
                                }
                                // Update subtotal
                                $subtotal = collect($products)->sum(function ($item) {
                                    return $item['quantity'] * $item['price'];
                                });
                                $set('subtotal', $subtotal);
                                // Update total
                                $shippingCost = $get('shipping_cost') ?? 0;
                                $set('total', $subtotal + $shippingCost);
                                $set('product_id', null, false);
                            }
                        }
                    }),
                Repeater::make('selected_products')
                    ->label('Selected Products')
                    ->schema([
                        TextInput::make('name')
                            ->label('Product Name')
                            // ->disabled()
                            ->required()
                            ->columnSpan(6),
                        TextInput::make('price')
                            ->label('Price')
                            // ->disabled()
                            ->required()
                            ->prefix('BDT')
                            ->columnSpan(3),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $products = $get('selected_products') ?? [];
                                $subtotal = collect($products)->sum(function ($item) {
                                    return $item['quantity'] * $item['price'];
                                });
                                $set('subtotal', $subtotal);
                                $shippingCost = $get('shipping_cost') ?? 0;
                                $set('total', $subtotal + $shippingCost);
                            })
                            ->columnSpan(3),
                    ])
                    ->columns(12)
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                    ->deletable()
                    ->addable(false)
                    ->reorderable(false)
                    ->default([])
                    ->required(),
            ])->columns(1)
                ->visible(fn(string $operation): bool => $operation === 'create'),

            Section::make('Order Details')->schema([
                TextInput::make('subtotal')
                    ->default(0)
                    ->prefix('BDT')
                    ->reactive(),
                TextInput::make('total')
                    ->default(0)
                    ->prefix('BDT')
                    ->reactive(),

                DateTimePicker::make('shipped_at')
                    ->label('Shipped Date')
                    ->placeholder('N/A'),

                DateTimePicker::make('delivered_at')
                    ->label('Delivered Date')
                    ->placeholder('N/A'),

                DateTimePicker::make('canceled_at')
                    ->label('Canceled Date')
                    ->placeholder('N/A'),

            ])->columns(3)->visible(fn(string $operation): bool => $operation === 'edit'),

            Section::make('Shipping Details')->schema([
                TextInput::make('shipping_cost')
                    ->label('Shipping Cost')
                    ->numeric()
                    ->required()
                    ->prefix('BDT')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $subtotal = $get('subtotal') ?? 0;
                        $set('total', $subtotal + ($state ?? 0));
                    }),
                Select::make('payment_method')
                    ->label('Payment Method')
                    ->searchable()
                    ->required()
                    ->options([
                        'cod' => 'COD',
                        'cash' => 'Cash',
                        'card' => 'Card',
                    ]),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ]),
                TextInput::make('shipping_address')
                    ->label('Shipping Address')
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->label('Order ID'),
                TextColumn::make('customer.name')->searchable()->sortable()->label('Customer Name'),
                TextColumn::make('orderItems_count')
                    ->label('Total Items')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->orderItems->count()),
                TextColumn::make('total')->money('bdt')->sortable(),
                TextColumn::make('status')
                    ->badge(fn($state) => match ($state) {
                        'pending' => 'primary',
                        'processing' => 'danger',
                        'shipped' => 'primary',
                        'completed' => 'primary',
                        'canceled' => 'primary',
                    })
                    ->sortable()
                    ->label('Status'),
                TextColumn::make('created_at')->label('Order at')->dateTime()->sortable(),
            ])
            ->actions([
                ActionGroup::make([
                    // Action::make('view-order-details')
                    //     ->label('Order Details')
                    //     ->icon('heroicon-o-currency-dollar')
                    //     ->color('info')
                    //     ->url(fn($record) => url('admin/orders/details', ['id' => $record->id])),
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ])
                    ->label('Order Status')
            ])
            ->bulkActions([])
            ->modifyQueryUsing(fn($query) => $query->latest());
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Components\Section::make('Customer Details')
                    ->schema([
                        Components\TextEntry::make('customer.name')->label('Name'),
                        Components\TextEntry::make('customer.email')->label('Email'),
                        Components\TextEntry::make('customer.phone')->label('Phone'),
                        Components\TextEntry::make('customer.zip_code')->label('Zip Code'),
                        Components\TextEntry::make('customer.address')->label('Address'),
                        Components\TextEntry::make('customer.city')->label('City'),
                        Components\TextEntry::make('customer.country')->label('Country'),
                        Components\TextEntry::make('customer.notes')->label('Notes')->columnSpan(2),
                    ])
                    ->columns(3),

                Components\Section::make('Order Details')
                    ->schema([
                        Components\TextEntry::make('payment_method')->label('Payment Method'),
                        Components\TextEntry::make('order_number')->label('Order Number'),
                        Components\TextEntry::make('subtotal')->money('bdt'),
                        Components\TextEntry::make('shipping_cost')->money('bdt'),
                        Components\TextEntry::make('total')->money('bdt'),
                        Components\TextEntry::make('status')->badge()
                            ->color(function ($state) {
                                return match ($state) {
                                    'pending' => 'primary',
                                    'processing' => 'gray',
                                    'shipped' => 'danger',
                                    'completed' => 'success',
                                    'canceled' => 'info',
                                    default => 'secondary',
                                };
                            }),
                    ])
                    ->columns(3),

                Components\Section::make('Order Shipping Address')
                    ->schema([
                        Components\TextEntry::make('billing_phone')->label('Shipping Phone')->columnSpan(2),
                        Components\TextEntry::make('shipping_zone')->label('Shipping Zone')->columnSpan(1),
                        Components\TextEntry::make('billing_address')->label('Shipping Address')->columnSpan(3),
                    ])
                    ->columns(6),

                Components\Section::make('Order Tracking')
                    ->schema([
                        Components\TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('N/A'),

                        Components\TextEntry::make('shipped_at')
                            ->label('Shipped Date')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('N/A'),

                        Components\TextEntry::make('delivered_at')
                            ->label('Delivered Date')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('N/A'),

                        Components\TextEntry::make('canceled_at')
                            ->label('Canceled Date')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('N/A')
                            ->visible(fn($record) => !is_null($record->canceled_at)),
                    ])
                    ->columns(3),
            ]);
    }

    public function openOrderDetailsModal(Order $order)
    {
        // Trigger the modal with the order items data
        $this->dispatchBrowserEvent('open-order-details-modal', [
            'orderItems' => $order->orderItems, // Pass order items data
        ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'details' => Pages\OrderDetails::route('/details/{id}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
