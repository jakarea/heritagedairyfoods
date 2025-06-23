<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoService
{

    protected $clientId;
    protected $clientSecret;
    protected $apiUrl;
    protected $storeId;

    public function __construct()
    {
        $this->clientId = env('PATHAO_CLIENT_ID');
        $this->clientSecret = env('PATHAO_CLIENT_SECRET');
        $this->apiUrl = env('PATHAO_API_URL');
        $this->storeId = env('PATHAO_STORE_ID');
    }

    // pathao shipment
    public function createOrder($orderId)
    {
        throw new \Exception('Pathao Not implemented.');

        try {
            $order = Order::findOrFail($orderId);
            $token = $this->createAccessToken();

            if (!$token) {
                return response()->json(['error' => 'Token fetch failed'], 500);
            }

            $payload = [
                'store_id' => $this->storeId,
                'merchant_order_id' => $order->order_number,
                'recipient_name' => $order->customer?->name ?? 'Test Recipient',
                'recipient_phone' => $order->customer?->phone ?? '01700000000',
                'recipient_address' => $order->shipping_address ?? $order->billing_address ?? 'Test Address',
                'recipient_city' => 1,
                'recipient_zone' => 298,
                'recipient_area' => 3,
                'delivery_type' => 48,
                'item_type' => 2,
                'special_instruction' => $order->order_notes ?? '',
                'item_quantity' => $order->orderItems->count(),
                'item_weight' => 1,
                'item_description' => $order->orderItems->map(function ($item) {
                    return "{$item->quantity}x {$item->product->name} (৳{$item->price})";
                })->implode(', '),
                'amount_to_collect' => (int) $order->total,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/aladdin/api/v1/orders", $payload);

            dd($response->json());

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Pathao order creation failed',
                    'details' => $response->json(),
                ], $response->status());
            }

            return response()->json([
                'message' => 'Order successfully created on Pathao',
                'data' => $response->json(),
            ], 201);

            
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'An unexpected error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // create access toekn
    protected function createAccessToken()
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/aladdin/api/v1/issue-token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'password',
                'username' => 'test@pathao.com',
                'password' => 'lovePathao',
            ]);

            if ($response->failed()) {
                throw new \Exception('Failed to get access token from Pathao.');
            }

            $result = $response->json();

            if (!isset($result['access_token'])) {
                throw new \Exception('Access token not found in response.');
            }

            return $result['access_token'];
        } catch (\Exception $e) {
            return null; // or rethrow if preferred
        }
    }
}
