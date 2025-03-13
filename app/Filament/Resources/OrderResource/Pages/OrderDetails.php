<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderInfo;



class OrderDetails extends Page
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.order-details';

    public $order;
    public $orderItems;
    public $products = [];
    public $status;

    public function mount($id): void
    {
        $this->order = Order::findOrFail($id);
        $this->orderItems = $this->order->orderItems;
        $this->status = $this->order->status;

        $jsonPath = storage_path('app/public/products.json');

        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $products = json_decode($jsonContent, true);

            foreach ($products as $product) {
                $newProduct[$product['id']] = $product;
            }

            $this->products = $newProduct;
        }
    }

    public function updateStatus($status)
    {
        DB::table('orders')->where('id', $this->order->id)->update(['status' => $status]);
        $this->order->status = $status; 
        $this->dispatch('statusUpdated', $status); 
    }

    public function sendEmail($subject)
    {
        $body = [
            'order' => $this->order,
            'orderItems' => $this->orderItems,
            'products' => $this->products,
        ];

        $mail = "heritagedairyfoods@gmail.com";
        Mail::to($mail)->send(new OrderInfo($subject, $body));
    }
}
