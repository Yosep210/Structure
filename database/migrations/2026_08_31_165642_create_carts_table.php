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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('cart_code')->nullable();
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->string('type', 50)->default('shop_order')->index();
            $table->nullableMorphs('buyable');
            $table->foreignId('varian_id')->nullable()->index()->constrained('product_varians');
            $table->string('name');
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->unsignedInteger('total_item')->default(1);
            $table->json('items')->nullable();
            $table->tinyInteger('status')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
