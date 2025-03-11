<?php

namespace App\Livewire;

use Livewire\Component;

class ProductList extends Component
{
    public $products = []; 

    public function mount()
    {
        // Load the JSON file
        $jsonPath = storage_path('app/public/products.json');

        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $this->products = json_decode($jsonContent, true);
        }
    }

    public function addToCart($productId)
    {
        // Emit the event to Cart component
        $this->dispatch('addToCartEvent', $productId); 
    } 

    public function render()
    {
        return view('livewire.product-list');
    }
}
