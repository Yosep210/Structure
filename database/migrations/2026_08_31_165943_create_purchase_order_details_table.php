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
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders');
            $table->foreignId('supplier_id')->constrained('suppliers'); // denormalisasi sengaja
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('varian_id')->nullable()->constrained('product_varians');
            $table->string('type')->nullable();
            $table->string('varian')->nullable(); // snapshot nama varian
            $table->string('image')->nullable();
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('omzet', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('additional_cost', 15, 2)->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('subtotal_bv', 15, 2)->default(0);
            $table->decimal('subtotal_omzet', 15, 2)->default(0);
            $table->decimal('subtotal_weight', 10, 2)->default(0);
            $table->decimal('subtotal_cost', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
    }
};
