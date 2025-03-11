<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Cart extends Component
{
    protected $listeners = ['addToCartEvent' => 'addToCart'];

    public $products = [];
    public $cartItems = [];
    public $sessionId;
    
    public $shipingValue = 60;
    public $name;
    public $address;
    public $phone_number;
    public $inside_dhaka;
    public $outside_dhaka;

    // Define validation rules
    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone_number' => 'required|regex:/^\+?\d{10,15}$/', // Basic phone validation
        'inside_dhaka' => 'nullable|boolean',
        'outside_dhaka' => 'nullable|boolean',
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
            $newPrice = $newQuantity * $product['price']; // Correct price calculation

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

    public function submit()
    {
        $this->validate();  
        
        

    }


    public function render()
    {
        return view('livewire.cart');
    }
}
