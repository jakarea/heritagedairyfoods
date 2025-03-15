<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /** 🔹 Hide the "New Order" button */
    protected static bool $canCreate = false;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Order Details')->schema([
                TextInput::make('id')->disabled()->hidden(),
                TextInput::make('customer_name')->label('Customer Name'),
                TextInput::make('customer_phone')->label('Phone'),
                TextInput::make('customer_address')->label('Address'),
                TextInput::make('order_number')->label('Order Number')->disabled(),
                TextInput::make('total_price')->label('Total Price')->prefix('BDT'),
                TextInput::make('shipping_cost')->label('Shipping Cost')->prefix('BDT'), 
                Select::make('shipping_zone')
                    ->label('Shipping Zone')
                    ->options([
                        'inside_dhaka' => 'Inside of Dhaka',
                        'outside_dhaka' => 'Outside of Dhaka', 
                    ]),
                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cod' => 'COD',
                        'bkash' => 'Bkash',
                        'nagad' => 'Nagad',
                        'rocket' => 'Rocket',
                        'card' => 'Card',
                    ]), // Disable for viewing
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ]), // Disable for viewing
                TextInput::make('created_at')->label('Order time')->disabled(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('order_number')->searchable()->label('Order ID'),
                TextColumn::make('customer_name')->searchable()->sortable()->label('Name'),
                TextColumn::make('customer_phone')->searchable()->label('Phone'),
                TextColumn::make('customer_address')->searchable()->label('Address')->limit(50),
                TextColumn::make('total_price')->money('bdt')->sortable(),
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
                    Action::make('view-order-details')
                        ->label('Order Details')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('info')
                        ->url(fn($record) => url('admin/orders/details', ['id' => $record->id])),
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

    public function openOrderDetailsModal(Order $order)
    {
        // Trigger the modal with the order items data
        $this->dispatchBrowserEvent('open-order-details-modal', [
            'orderItems' => $order->orderItems, // Pass order items data
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'details' => Pages\OrderDetails::route('/details/{id}'),
        ];
    }
}
