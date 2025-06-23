<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuponResource\Pages; 
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User; 
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table; 
use Illuminate\Support\Facades\Hash; 
use Filament\Forms\Components\{TextInput, Select, Textarea, FileUpload, Toggle, Section, DateTimePicker};

class CuponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationBadgeTooltip = 'The number of cupons';
    protected static ?string $navigationGroup = 'Marketing Management';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Cupon Details')->schema([
                    TextInput::make('code')
                        ->required()
                        ->unique(Coupon::class, 'code', ignoreRecord: true)
                        ->maxLength(12)
                        ->minLength(4),

                    Select::make('type')
                        ->options([
                            'flat' => 'Flat',
                            'percentage' => 'Percentage',
                            'bogo' => 'Buy One Get One',
                        ])
                        ->required()
                        ->reactive(),

                    // if type is flat or percentage
                    TextInput::make('discount_amount')
                        ->numeric()
                        ->requiredIf('type', ['flat', 'percentage'])
                        ->visible(fn($get) => in_array($get('type'), ['flat', 'percentage']))
                        ->dehydrated(fn($get) => in_array($get('type'), ['flat', 'percentage'])),

                    // if type is bogo
                    Select::make('buy_product_ids')
                        ->label('Buy Products')
                        ->options(function () {
                            return Product::pluck('name', 'id');
                        })
                        ->requiredIf('type', ['bogo'])
                        ->visible(fn($get) => in_array($get('type'), ['bogo']))
                        ->dehydrated(fn($get) => in_array($get('type'), ['bogo']))
                        ->multiple()
                        ->preload(),

                    Select::make('get_product_ids')
                        ->label('Get Products')
                        ->options(function () {
                            return Product::pluck('name', 'id');
                        })
                        ->requiredIf('type', ['bogo'])
                        ->visible(fn($get) => in_array($get('type'), ['bogo']))
                        ->dehydrated(fn($get) => in_array($get('type'), ['bogo']))
                        ->multiple()
                        ->preload(),

                    TextInput::make('min_cart_value')
                        ->numeric()
                        ->nullable()
                        ->minValue(0),

                    TextInput::make('limits')
                        ->label('Customer Limits')
                        ->numeric()
                        ->minValue(0)
                        ->nullable()
                        ->helperText('Total allowed Customer'),

                    TextInput::make('per_user_limit')
                        ->numeric()
                        ->label('Uses per customer')
                        ->minValue(0)
                        ->nullable(),

                    Select::make('users')
                        ->label('Select Customer')
                        ->options(function () {
                            return Customer::pluck('name', 'id');
                        })
                        ->searchable()
                        ->nullable()
                        ->multiple()
                        ->preload()
                        ->reactive()
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
                        ->createOptionUsing(function (array $data) {
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

                            return $customer->id;
                        }),
                    Select::make('applies_to_product_ids')
                        ->label('Select Products')
                        ->options(function () {
                            return Product::pluck('name', 'id');
                        })
                        ->searchable()
                        ->nullable()
                        ->multiple()
                        ->preload(),

                    Select::make('applies_to_category_ids')
                        ->label('Select Categories')
                        ->options(function () {
                            return Category::pluck('name', 'id');
                        })
                        ->searchable()
                        ->nullable()
                        ->multiple()
                        ->preload(),

                    DateTimePicker::make('end_date')
                        ->nullable()
                        ->minDate(now()),

                    Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                        ])
                        ->default('active')
                        ->required(),
                ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->formatStateUsing(fn($state, $record) => $record->type !== 'bogo' ? ($record->type === 'percentage' ? "$state%" : "$state") : '-')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('min_cart_value')
                    ->placeholder('0')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()
                    ->placeholder('N/A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => $state === 'active' ? 'success' : 'danger')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'flat' => 'Flat',
                        'percentage' => 'Percentage',
                        'bogo' => 'Buy One Get One',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCupons::route('/'),
            'create' => Pages\CreateCupon::route('/create'),
            'view' => Pages\ViewCupon::route('/{record}'),
            'edit' => Pages\EditCupon::route('/{record}/edit'),
        ];
    }
}
