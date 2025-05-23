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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');
            $table->string('image_path', 255);
            $table->boolean('is_primary')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['product_id', 'variation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_product_images');
    }
};
