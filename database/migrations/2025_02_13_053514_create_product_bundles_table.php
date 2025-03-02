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
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('bundled_product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_flat', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bundles');
    }
};
