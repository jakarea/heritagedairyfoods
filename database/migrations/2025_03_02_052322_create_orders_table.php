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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('order_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');
            $table->string('order_number')->unique();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('tax_amount', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->enum('payment_method', ['cod', 'card', 'paypal', 'stripe', 'bank_transfer'])->default('cod');
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_carrier')->nullable();
            $table->longText('billing_address')->nullable();
            $table->longText('shipping_address')->nullable();
            $table->text('order_notes')->nullable();
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'canceled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
