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
            $table->string('subtitle')->nullable(); // New: For product subtitle
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_desc')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('search_keywords')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('offer_price', 10, 2)->nullable(); 
            $table->enum('discount_in', ['flat', 'percentage'])->default('flat');
            $table->string('image')->nullable();
            $table->string('sku')->unique();
            $table->enum('status', ['active', 'draft', 'out_of_stock', 'archived'])->default('active');
            $table->enum('type', ['small', 'medium', 'large'])->default('medium'); 
            $table->string('weight')->nullable();  
            $table->json('categories');
            $table->json('tags');
            $table->json('video')->nullable(); 
            $table->json('details')->nullable(); 
            $table->json('conclusion')->nullable(); 
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
