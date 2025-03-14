<?php

namespace App\Livewire;

use Livewire\Component;

class ProductDetailsPage extends Component
{
    public $productSlug;
    public $product;

    public function mount($productSlug)
    {
        $this->productSlug = $productSlug;

        // Load the product data from a JSON file (adjust path as needed)
        $jsonData = file_get_contents(storage_path('app/public/products.json'));

        // Decode the JSON data into a PHP array
        $products = json_decode($jsonData, true); // true to return as an array

        // Find the product with the corresponding ID
        $this->product = collect($products)->firstWhere('slug', $this->productSlug);
    }

    public function render()
    {
        return view('livewire.product-details-page');
    }
}
