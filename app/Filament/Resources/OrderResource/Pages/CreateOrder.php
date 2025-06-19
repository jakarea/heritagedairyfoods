<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderItem;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    use \App\Traits\RedirectIndex;

    protected static string $resource = OrderResource::class;


    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $order = $this->record;
            $data = $this->form->getState();


            if (!empty($data['selected_products']) && is_array($data['selected_products'])) {
                // dd($data['selected_products']);
                foreach ($data['selected_products'] as $product) {
                    OrderItem::create([
                        'order_id'     => $order['id'],
                        'product_id'   => $product['product_id'],
                        'variation_id' => $product['variation_id'] ?? null,
                        'quantity'     => $product['quantity'] ?? 1,
                        'price'        => $product['price'] ?? 0,
                        'subtotal'     => ($product['price'] ?? 0) * ($product['quantity'] ?? 1),
                        'discount'     => $product['discount'] ?? 0,
                    ]);
                }
            }
        });
    }
}
