<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $price = $product->discount_price ?? $product->base_price;
                                $set('price', $price);
                                $quantity = 1; // Default quantity
                                $set('subtotal', $price * $quantity);
                            }
                        }
                    }),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $price = $get('price') ?? 0;
                        $set('subtotal', $price * ($state ?? 1));
                    }),

                Forms\Components\TextInput::make('price')
                    ->label('Unit Price')
                    ->numeric()
                    ->prefix('BDT')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $quantity = $get('quantity') ?? 1;
                        $set('subtotal', ($state ?? 0) * $quantity);
                    }),

                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->prefix('BDT')
                    ->dehydrated(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->prefix('BDT ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->prefix('BDT ')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->before(function (array $data) {
                        $order = $this->getOwnerRecord();
                        $exists = $order->orderItems()->where('product_id', $data['product_id'])->exists();

                        if ($exists) {
                            \Filament\Notifications\Notification::make()
                                ->title('Duplicate Product')
                                ->body('This product is already added to the order. Please edit the existing item or choose a different product.')
                                ->danger()
                                ->send();
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'product_id' => ['This product is already added to the order.'],
                            ]);
                        }
                    })
                    ->after(function (array $data, string $model) {
                        $this->updateOwnerRecord();
                        return redirect(request()->header('Referer'));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->after(function (array $data, string $model) {
                    $this->updateOwnerRecord();
                    return redirect(request()->header('Referer'));
                }),
                Tables\Actions\DeleteAction::make()->after(function (array $data, string $model) {
                    $this->updateOwnerRecord();
                    return redirect(request()->header('Referer'));
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->after(function (array $data, string $model) {
                        $this->updateOwnerRecord();
                        return redirect(request()->header('Referer'));
                    }),
                ]),
            ]);
    }

    protected function updateOwnerRecord(): void
    {
        $order = $this->getOwnerRecord();
        $orderItems = $this->getOwnerRecord()->orderItems;
        $totalSubtotal = $orderItems->sum('subtotal');

        $order->subtotal = $totalSubtotal;
        $order->total = $totalSubtotal + $order->shipping_cost;

        $order->save();
    }
}
