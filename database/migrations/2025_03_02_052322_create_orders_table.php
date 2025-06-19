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
            $table->string('session_id')->nullable();
            $table->foreignId('order_by')->nullable()->constrained('users')->onDelete('set null');

            $table->string('order_number')->unique();
            $table->string('payment_id')->nullable();

            $table->string('discount_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->decimal('subtotal_price', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);

            $table->string('billing_address')->nullable();
            $table->string('billing_phone')->nullable();
            $table->enum('shipping_zone', ['dhaka', 'rangpur', 'rajshahi', 'khulna', 'barishal', 'chitagong', 'sylhet', 'mymensingh'])->default('dhaka');

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            
            $table->enum('payment_method', ['cod', 'bkash', 'nagad', 'rocket', 'card'])->default('cod');
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
