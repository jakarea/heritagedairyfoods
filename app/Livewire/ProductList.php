<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProductList extends Component
{

    public $products = [];  

    public function mount()
    {
        $products = Product::all();
        $this->products = $products;
    }

    public function addProductToCart($productId)
    {
        // Trigger the event to update the Cart component
        $this->dispatch('addToCartEvent', $productId);
    }


    public function render()
    {
        return view('livewire.product-list');
    }
}
