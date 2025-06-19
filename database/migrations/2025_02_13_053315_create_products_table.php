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
            $table->string('name', 255);
            $table->string('subtitle', 255)->nullable();
            $table->string('slug', 255)->unique();
            $table->text('short_desc')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->enum('discount_in', ['flat', 'percentage'])->default('flat');
            $table->integer('stock')->default(0);
            $table->string('sku', 50)->unique()->nullable();
            $table->enum('status', ['active', 'draft', 'out_of_stock', 'archived'])->default('active');
            $table->enum('type', ['simple', 'variable', 'bundle'])->default('simple');   
            $table->json('categories')->nullable(); 
            $table->json('tags')->nullable();  
            $table->string('video_url', 255)->nullable();   
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('search_keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('slug');
            $table->index('sku');
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
