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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['flat', 'percentage', 'bogo']);
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->text('buy_product_ids')->nullable();
            $table->text('get_product_ids')->nullable();
            $table->decimal('min_cart_value', 10, 2)->nullable();
            $table->integer('limits')->nullable();
            $table->integer('per_user_limit')->nullable();
            $table->text('users')->nullable();
            $table->text('applies_to_product_ids')->nullable();
            $table->text('applies_to_category_ids')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
