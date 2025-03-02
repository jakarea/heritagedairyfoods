<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            throw new \Exception("The 'categories' table must be migrated first.");
        }

            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique(); // SEO-friendly URL
                $table->text('description')->nullable();
                $table->text('short_desc')->nullable();
                $table->string('meta_title')->nullable(); // SEO Meta Title
                $table->text('meta_description')->nullable(); // SEO Meta Description
                $table->string('meta_keywords')->nullable(); // SEO Meta Keywords
                $table->text('search_keywords')->nullable(); // Search optimization
                $table->decimal('price', 10, 2); // Base price (if no variation)
                $table->decimal('discount_price', 10, 2)->nullable();
                $table->enum('discount_in', ['flat', 'percentage'])->default('flat');
                $table->integer('stock')->nullable(); // NULL means variations manage stock
                $table->string('sku')->unique();
                $table->enum('status', ['active', 'draft', 'out_of_stock', 'archived'])->default('active');
                $table->json('categories'); // Store multiple category IDs
                $table->json('tags'); // Store multiple tag names
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
