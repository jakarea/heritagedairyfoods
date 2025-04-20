<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class ProductDetailsPage extends Component
{
    public $productSlug;
    public $product;

    public function mount($productSlug)
    {
        $this->productSlug = $productSlug;

        $this->product = Product::where('slug', $productSlug)->first();
    }

    public function render()
    {
        return view('livewire.product-details-page');
    }
}
