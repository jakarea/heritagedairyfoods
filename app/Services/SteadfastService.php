<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class SteadfastService
{

    protected $apiUrl;
    protected $apiKey;
    protected $secretKey; 

    public function __construct()
    {
        $this->apiUrl = env('STEADFAST_API_URL');
        $this->apiKey = env('STEADFAST_API_KEY');
        $this->secretKey = env('STEADFAST_SECRET_KEY'); 
    }

    // steadfast shipment
    public function createOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
            'Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/create_order", [
            'invoice' => 'GIOPIO_' . $order->order_number . '_' . uniqid(),
            'recipient_name' => $order->customer ? $order->customer->name : 'Test Recipient',
            'recipient_phone' => $order->customer ? $order->customer->phone : '01700000000',
            'alternative_phone' => '01700000000',
            'recipient_email' => $order->customer ? $order->customer->email : 'test_customer@yopmail.com',
            'recipient_address' => $order->shipping_address ? $order->shipping_address : ($order->billing_address ?? 'Test Address'),
            'cod_amount' => $order->total ?? 0,
            'total_lot' => $order->orderItems->count(),
            'delivery_type' => (int) $order->shipping_method,
            'note' =>  $order->order_notes,
            'item_description' => $order->orderItems->map(function ($item) {
                return "{$item->quantity}x {$item->product->name} (৳{$item->price})";
            })->implode(', '),
        ]);

        $result = $response->json();

        if ($result['status'] === 200) {

            $res = $result['consignment'];
            $order->update([
                'consignment_id' => $res['consignment_id'],
                'invoice' => $res['invoice'],
                'tracking_code' => $res['tracking_code'],
                'courier_status' => $res['status'],
                'status' => 'processing',
            ]);

            return ['message' => 'Test shipment created for order #' . $order->order_number, 'data' => $res];
        }

        $data = $response->json();
        $errors = $data['errors'] ?? [];
        $errorMessage = $data['message'] ?? 'Failed to create test order';

        // If there are validation errors, flatten and concatenate them
        if (!empty($errors)) {
            $flattened = collect($errors)->flatten()->implode('; ');
            $errorMessage .= ' — ' . $flattened;
        }

        throw new \Exception($errorMessage);
    }

    // track steadfast shipment
    public function trackOrder($trackingCode, $orderId)
    {
        $order = \App\Models\Order::findOrFail($orderId);

        $response = Http::withHeaders([
            'Api-Key' => $this->apiKey,
            'Secret-Key' => $this->secretKey,
            'Content-Type' => 'application/json',
        ])->get("{$this->apiUrl}/status_by_trackingcode/{$trackingCode}");

        if ($response->successful()) {
            $result = $response->json();

            if (isset($result['status']) && $result['status'] === 200 && isset($result['delivery_status'])) {
                $status = $result['delivery_status'];
                $order->update([
                    'courier_status' => $status,
                ]);

                return [
                    'message' => "Shipment Order status is: {$status}",
                    'order' => $order->order_number
                ];
            }

            // \Log::error('Steadfast API Invalid Response:', $result);
            throw new \Exception('Invalid response from Steadfast API');
        }

        // \Log::error('Steadfast API Error:', $response->json());
        throw new \Exception($response->json()['message'] ?? 'Failed to track shipment');
    }

}
