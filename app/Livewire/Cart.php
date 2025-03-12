<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class Cart extends Component
{
    protected $listeners = ['addToCartEvent' => 'addToCart'];

    public $products = [];
    public $cartItems = [];
    public $sessionId;

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
        'phone_number' => 'required|regex:/^\+?\d{8,20}$/',
        'shiping_zone' => 'required|string',
    ];

    protected $messages = [
        'name.required' => 'নাম অবশ্যই প্রদান করতে হবে।',
        'name.string' => 'নাম একটি বৈধ স্ট্রিং হতে হবে।',
        'name.max' => 'নামের দৈর্ঘ্য ২৫৫ অক্ষরের বেশি হতে পারে না।',

        'address.required' => 'ঠিকানা অবশ্যই প্রদান করতে হবে।',
        'address.string' => 'ঠিকানা একটি বৈধ স্ট্রিং হতে হবে।',
        'address.max' => 'ঠিকানার দৈর্ঘ্য ২৫৫ অক্ষরের বেশি হতে পারে না।',

        'phone_number.required' => 'ফোন নম্বর অবশ্যই প্রদান করতে হবে।',
        'phone_number.regex' => 'ফোন নম্বরটি একটি বৈধ ফোন নম্বর হতে হবে (+এবং ৮-২০ অঙ্কের মধ্যে)।',

        'shiping_zone.required' => 'শিপিং জোন অবশ্যই সিলেক্ট করতে হবে।',
    ];

    public function validateCartItems()
    {
        $this->validate([
            'cartItems' => 'required|array|min:1',
        ]);
    }

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
        $cartId = $this->getCartId();

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

    public function isProductInCart($productId)
    {
        $cartId = $this->getCartId();

        // Check if the product exists in the cart
        $cartItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        return $cartItem ? true : false;
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
        $this->isProductInCart($productId);
    }

    public function removeFromCart($productId)
    {
        $cartId = $this->getCartId();
        DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->delete();

        $this->updateCart();
        $this->isProductInCart($productId);
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
        $this->isProductInCart($productId);
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
        $this->isProductInCart($productId);
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
        $cartId = $this->getCartId();

        return (float) DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->sum('price');
    }

    public function submit()
    {
        $validated = $this->validate();
        $this->total_price = $this->calculateTotalPrice();

        if ($validated) {
            try {
                 
                // Insert the order
                $orderId = DB::table('orders')->insertGetId([  // Use insertGetId to get the order_id
                    'customer_id' => $this->sessionId,
                    'order_number' => Str::upper(Str::random(8)),
                    'total_price' => (float)$this->total_price + (float)$this->shipingValue, // Ensure it's a float
                    'shipping_cost' => (float)$this->shipingValue, // Ensure it's a float
                    'shipping_zone' => $this->shiping_zone,
                    'payment_method' => 'COD',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert order items using cart items
                $mainCart = DB::table('carts')->where('session_id', $this->sessionId)->first();
                $cartItems = DB::table('cart_items')->where('cart_id', $mainCart->id)->get();

                $orderItems = $cartItems->map(function ($cartItem) use ($orderId) {
                    return [
                        'order_id' => $orderId,
                        'product_id' => $cartItem->product_id,
                        'variation_id' => null,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price, // assuming the price field in cart_items
                        'subtotal' => $cartItem->price, // Subtotal calculation
                        'discount' => 0, // Assuming there might be a discount field, default to 0 if null
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });

                // dd($orderItems);

                // Insert all order items in bulk
                DB::table('order_items')->insert($orderItems->toArray());

                DB::table('cart_items')->where('cart_id', $mainCart->id)->delete();

                $this->updateCart();

                // Flash success message
                session()->flash('success', 'Your order has been placed successfully!');

                // Reset fields after successful order placement
                $this->reset(['name', 'sessionId', 'address', 'phone_number', 'shiping_zone', 'total_price', 'shipingValue']);
            } catch (\Exception $e) {
                session()->flash('error', 'There was a problem placing your order! ' . $e->getMessage());
            }
        } else {
            session()->flash('error', 'Incorrect Data');
        }
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
