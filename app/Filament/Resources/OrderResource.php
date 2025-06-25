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
use Filament\Tables\Actions\Action;
use App\Services\SteadfastService;
use App\Services\PathaoService;
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

                            // Populate address fields
                            if ($address = $customer->address) {
                                $addressParts = [
                                    $address->address_line_1,
                                    $address->address_line_2,
                                    $address->country,
                                    $address->division?->name,
                                    $address->district?->name,
                                    $address->thana?->name,
                                    $address->zip_code,
                                ];

                                $set('billing_address', implode(', ', array_filter($addressParts)));
                                $set('shipping_address', implode(', ', array_filter($addressParts)));
                            }
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

                TextInput::make('order_number')
                    ->required()
                    ->default(function () {
                        do {
                            $orderNumber = Str::upper(Str::random(8));
                        } while (Order::where('order_number', $orderNumber)->exists());
                        return $orderNumber;
                    })
                    ->required(),
                TextInput::make('email')->label('Email')->disabled()->visible(fn(string $operation): bool => $operation === 'create'),
                TextInput::make('phone')->label('Phone'),
            ])->columns(2),

            // billing info
            Section::make('Adress Details')->schema([
                TextInput::make('billing_address')->label('Billing Address')
                    ->nullable()->columnSpanFull(),

                TextInput::make('shipping_address')->label('Shipping Address')
                    ->nullable()->columnSpanFull(),
            ])->columns(4),

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
                            ->required()
                            ->columnSpan(6),
                        TextInput::make('price')
                            ->label('Price')
                            ->reactive()
                            ->required()
                            ->prefix('BDT')
                            ->columnSpan(3)
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $products = $get('../../selected_products') ?? [];
                                $subtotal = collect($products)->sum(function ($item) {
                                    return $item['quantity'] * $item['price'];
                                });
                                $set('../../subtotal', $subtotal);
                                $shippingCost = $get('../../shipping_cost') ?? 0;
                                $set('../../total', $subtotal + $shippingCost);
                            }),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $products = $get('../../selected_products') ?? [];
                                $subtotal = collect($products)->sum(function ($item) {
                                    return $item['quantity'] * $item['price'];
                                });
                                $set('../../subtotal', $subtotal);
                                $shippingCost = $get('../../shipping_cost') ?? 0;
                                $set('../../total', $subtotal + $shippingCost);
                            })
                            ->columnSpan(3),
                    ])
                    ->columns(12)
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                    ->deletable()
                    ->addable(false)
                    ->reorderable(false)
                    ->default([])
                    ->live()
                    ->required(),
            ])->columns(1)
                ->visible(fn(string $operation): bool => $operation === 'create'),

            Section::make('Order Details')->schema([

                Select::make('payment_method')
                    ->label('Payment Method')
                    ->searchable()
                    ->required()
                    ->options([
                        'cod' => 'COD',
                        'card' => 'Card',
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                    ]),

                Select::make('shipping_method')
                    ->nullable()
                    ->options([
                        '0' => 'Home Delivery',
                        '1' => 'Point Pickup',
                    ]),

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

                TextInput::make('subtotal')
                    ->default(0)
                    ->prefix('BDT')
                    ->nullable()
                    ->reactive(),
                TextInput::make('total')
                    ->default(0)
                    ->prefix('BDT')
                    ->nullable()
                    ->reactive(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ]),

                Textarea::make('order_notes')->nullable()->columnSpanFull(),

            ])->columns(3),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->label('Order ID'),
                TextColumn::make('consignment_id')->searchable()->label('Consigment ID'),
                TextColumn::make('tracking_code')->searchable()->label('Tracking ID'),
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
                TextColumn::make('courier_status')->sortable(),
                // TextColumn::make('created_at')->label('Order at')->dateTime()->sortable(),
            ])
            ->actions([

                // Send to Courier Button (visible when status is 'pending')
                Action::make('sendToSteadfastCourier')
                    ->label('Steadfast')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    // ->requiresConfirmation()
                    ->visible(fn($record) => !$record->consignment_id)
                    ->action(function ($record) {
                        $service = new SteadfastService();
                        try {
                            $response = $service->createOrder($record->id);
                            \Filament\Notifications\Notification::make()
                                ->title($response['message'])
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to send to courier')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('sendToPathaoCourier')
                    ->label('Pathao')
                    ->icon('heroicon-o-truck')
                    ->color('danger')
                    // ->requiresConfirmation()
                    ->visible(fn($record) => !$record->consignment_id)
                    ->action(function ($record) {
                        $service = new PathaoService();
                        try {
                            $response = $service->createOrder($record->id);
                            \Filament\Notifications\Notification::make()
                                ->title($response['message'])
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to send to courier')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Track Order Button (visible when status is 'processing')
                Action::make('trackOrder')
                    ->label('Track Order')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->visible(fn($record) => $record->consignment_id)
                    ->action(function ($record) {
                        $service = new SteadfastService();
                        try {
                            $response = $service->trackOrder($record->tracking_code, $record->id);
                            \Filament\Notifications\Notification::make()
                                ->title($response['message'])
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to track the courier')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                ActionGroup::make([
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
                        Components\TextEntry::make('customer.address.zip_code')->label('Zip Code'),
                        Components\TextEntry::make('customer.address.address_line_1')->label('Street Address One'),
                        Components\TextEntry::make('customer.address.address_line_2')->label('Street Address Two'),
                        Components\TextEntry::make('customer.address.division.name')->label('Division'),
                        Components\TextEntry::make('customer.address.district.name')->label('District'),
                        Components\TextEntry::make('customer.address.thana.name')->label('Thana'),
                        Components\TextEntry::make('customer.address.country')->label('Country'),
                        Components\TextEntry::make('customer.notes')->label('Notes')->columnSpan(2),
                    ])
                    ->columns(3),

                Components\Section::make('Order Details')
                    ->schema([
                        Components\TextEntry::make('coupon.code')->label('Cupon Code')->placeholder('N/A'),
                        Components\TextEntry::make('order_number')->label('Order Number'),
                        Components\TextEntry::make('shipping_method')->label('Shipping Method'),
                        Components\TextEntry::make('payment_method')->formatStateUsing(function ($state) {
                            return [
                                'cod' => 'Cash On Delivery',
                                'card' => 'Card',
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                            ][$state] ?? ucfirst($state);
                        })->label('Payment Method'),
                        Components\TextEntry::make('discount_amount')->label('Discount Amount')->money('bdt')->placeholder('0.00'),
                        Components\TextEntry::make('tax_amount')->label('Tax Amount')->money('bdt')->placeholder('0.00'),
                        Components\TextEntry::make('subtotal')->money('bdt')->placeholder('0.00'),
                        Components\TextEntry::make('shipping_cost')->money('bdt')->placeholder('0.00'),
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
                        Components\TextEntry::make('order_notes')->label('Order Notes')->columnSpan(2)->placeholder('N/A'),
                    ])
                    ->columns(3),

                Components\Section::make('Order Address')
                    ->schema([
                        Components\TextEntry::make('billing_address')->label('Billing Address'),
                        Components\TextEntry::make('shipping_address')->label('Shipping Phone'),
                    ])
                    ->columns(2),

                Components\Section::make('Order Tracking')
                    ->schema([

                        Components\TextEntry::make('consignment_id')->label('Consignment ID')->placeholder('N/A'),
                        Components\TextEntry::make('invoice')->label('Invoice ID')->placeholder('N/A'),
                        Components\TextEntry::make('tracking_code')->label('Tracking Code')->placeholder('N/A'),
                        Components\TextEntry::make('courier_status')->label('Courier Status')->placeholder('N/A'),

                        Components\TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('N/A'),
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
