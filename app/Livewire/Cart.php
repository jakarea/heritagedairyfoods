<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Cart extends Component
{
    protected $listeners = ['addToCartEvent' => 'addToCart'];

    public $products = [];
    public $cartItems = [];
    public $sessionId;
    public $isProductInCarts = [];

    public $shipingValue = 0;
    public $name;
    public $address;
    public $phone_number;
    public $shiping_zone;
    public $total_price = 0;

    // Define validation rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone_number' => 'required|regex:/^\+?\d{2,20}$/',
        'shiping_zone' => 'required|string',
    ];

    protected $messages = [
        'name.required' => 'নাম লিখতে ভুলে গেছেন',
        'name.string' => 'সঠিকভাবে শুধুই নাম লিখুন',
        'name.max' => 'একটু ছোট নাম লিখুন',

        'address.required' => 'ঠিকানা প্রদান করুন',
        'address.string' => 'সঠিকভাবে ঠিকানা দিন',
        'address.max' => 'ঠিকানা একটু ছোট করুন',

        'phone_number.required' => 'ফোন নম্বর দিতে হবে',
        'phone_number.regex' => 'ফোন নাম্বার সঠিক তো ?',

        'shiping_zone.required' => 'কোথায় ডেলিভেরি পেতে চান',
    ];

    public function mount()
    {
        $this->sessionId = Session::getId();
        $this->loadProducts();
        $this->getCartId();
        $this->updateCart();
 
    }

    private function loadProducts()
    {
        $jsonPath = storage_path('app/public/products.json');

        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $this->products = json_decode($jsonContent, true);
        }
    }

    private function getCartId()
    {
        $cart = DB::table('carts')->where('session_id', $this->sessionId)->first();

        if (!$cart) {
            $cartId = DB::table('carts')->insertGetId([
                'session_id' => $this->sessionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $cartId = $cart->id;
        } 
        return $cartId;
    }

    public function toggleCart($productId, $isChecked)
    {  
        
        if ($isChecked) {
            // Add the product to the cart
            $this->addToCart($productId);
        } else {
            // Remove the product from the cart
            $this->removeFromCart($productId);
        }

        // Optionally update the cart display or total after adding/removing
        $this->updateCart(); 
    }

    public function isProductInCart()
    {
        
        $cartId = $this->getCartId();

        // Check if the product exists in the cart
        $this->isProductInCarts  = DB::table('cart_items')
        ->where('cart_id', $cartId)
        ->pluck('product_id')
        ->toArray(); 
    }

    public function addToCart($productId)
    {
        $cartId = $this->getCartId();
        $product = $this->getProductById($productId);

        $cartItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->where('product_id', $productId)
                ->update([
                    'quantity' => $cartItem->quantity + 1,
                    'price' => ($cartItem->quantity + 1) * $this->getProductById($productId)['price'],
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => 1,
                'price' => $this->getProductById($productId)['price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->updateCart();
        // $this->isProductInCart();
    }

    public function removeFromCart($productId)
    {
        $cartId = $this->getCartId();
        DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->delete();

        $this->updateCart(); 
    }

    private function getProductById($productId)
    {
        foreach ($this->products as $product) {
            if ($product['id'] == $productId) {
                return $product;
            }
        }
        return null;
    }

    private function updateCart()
    {
        $cartId = $this->getCartId();

        $this->cartItems = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->get();

        $this->isProductInCart();

    }

    public function incrementQuantity($productId)
    {
        $cartId = $this->getCartId();

        $cartItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $product = $this->getProductById($productId);
            $newQuantity = $cartItem->quantity + 1;
            $newPrice = $newQuantity * $product['price'];

            DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->where('product_id', $productId)
                ->update([
                    'quantity' => $newQuantity,
                    'price' => $newPrice,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('cart_items')->insert([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => 1,
                'price' => $this->getProductById($productId)['price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Re-fetch cart to ensure the updated data is available
        $this->updateCart();
        // $this->isProductInCart();
    }

    public function decrementQuantity($productId)
    {
        $cartId = $this->getCartId();

        $cartItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem && $cartItem->quantity > 1) {
            // Get product details to ensure correct unit price
            $product = $this->getProductById($productId);

            // Decrement quantity by 1
            $newQuantity = $cartItem->quantity - 1;
            $newPrice = $newQuantity * $product['price'];

            DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->where('product_id', $productId)
                ->update([
                    'quantity' => $newQuantity,
                    'price' => $newPrice, // Update the price correctly
                    'updated_at' => now(),
                ]);
        } else {
            $cartId = $this->getCartId();
            DB::table('cart_items')
                ->where('cart_id', $cartId)
                ->where('product_id', $productId)
                ->delete();
        }

        // Re-fetch cart to ensure the updated data is available
        $this->updateCart();
        // $this->isProductInCart();
    }

    public function getCartItemQuantity($productId)
    {
        $cartId = $this->getCartId();

        $cartItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        return $cartItem ? $cartItem->quantity : 0; // Return 0 if the item is not in the cart
    }

    public function shipingType($value)
    {
        $this->shipingValue = $value;
    }

    public function calculateTotalPrice()
    {
        $cart = DB::table('carts')->where('session_id', $this->sessionId)->first();

        return (float) DB::table('cart_items')
            ->where('cart_id', $cart->id)
            ->sum('price');
    }

    public function clearCart()
    {
        $mainCart = DB::table('carts')->where('session_id', $this->sessionId)->first();
        DB::table('cart_items')->where('cart_id', $mainCart->id)->delete();
    }

    public function submit()
    {
        // Validate user input
        $validated = $this->validate();

        if (!$validated) {
            session()->flash('error', 'ভুল তথ্য');
            return;
        }

        // Ensure there are cart items
        $mainCart = DB::table('carts')->where('session_id', $this->sessionId)->first();
        if (!$mainCart) {
            session()->flash('error', 'কোন কার্ট পাওয়া যায়নি');
            return;
        }

        $cartItems = DB::table('cart_items')->where('cart_id', $mainCart->id)->get();
        if ($cartItems->isEmpty()) {
            session()->flash('error', 'কোন পণ্য নির্বাচন করা হয়নি');
            return;
        }

        // Calculate total price
        $this->total_price = $this->calculateTotalPrice();


        try {
            DB::beginTransaction();

            // Insert the order
            $orderNumber = Str::upper(Str::random(8));
            $orderId = DB::table('orders')->insertGetId([
                'customer_id' => $this->sessionId,
                'customer_name' => $this->name,
                'customer_phone' => $this->phone_number,
                'customer_address' => $this->address,
                'order_number' => $orderNumber,
                'total_price' => (float)$this->total_price + (float)$this->shipingValue,
                'shipping_cost' => (float)$this->shipingValue,
                'shipping_zone' => $this->shiping_zone,
                'payment_method' => 'COD',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert order items
            $orderItems = $cartItems->map(function ($cartItem) use ($orderId) {
                return [
                    'order_id' => $orderId,
                    'product_id' => $cartItem->product_id,
                    'variation_id' => null,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'subtotal' => $cartItem->price,
                    'discount' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            if (!empty($orderItems)) {
                DB::table('order_items')->insert($orderItems);
            }

            // Clear the cart
            $this->clearCart(); 
            $this->updateCart();
            
            DB::commit();
 
            // Reset form fields
            $this->reset(['name', 'address', 'phone_number', 'shiping_zone', 'total_price', 'shipingValue']);

            // Redirect to the order page
            session()->flash('success', 'আপনার অর্ডার সফলভাবে গ্রহন করা হয়েছে, অর্ডার নাম্বার ' . $orderNumber);

            return $this->redirect('/');    


        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'অর্ডার প্রদান করতে সমস্যা হচ্ছে' .$e->getMessage());
        }
    }


    public function render()
    {
        return view('livewire.cart');
    }
}
