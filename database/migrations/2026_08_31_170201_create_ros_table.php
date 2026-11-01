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
        Schema::create('ros', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->foreignId('activator_id')->index()->constrained('member_networks');
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->foreignId('stockist_id')->nullable()->index()->constrained('member_networks');
            $table->foreignId('shop_order_id')->index()->constrained('shop_orders');
            $table->string('type')->nullable()->index();
            $table->json('pins')->nullable();
            $table->json('meta')->nullable();
            $table->tinyInteger('status')->default(0)->index();
            $table->decimal('point', 15, 2)->default(0);
            $table->decimal('total_bv', 15, 2)->default(0);
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->unsignedSmallInteger('unique')->nullable();
            $table->decimal('shipping', 15, 2)->default(0);
            $table->decimal('shipping_discount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->string('voucher')->nullable();
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->boolean('autoro')->default(false);
            $table->decimal('total_checkout', 15, 2)->default(0);
            $table->decimal('total_payment', 15, 2)->default(0);
            $table->decimal('total_omzet', 15, 2)->default(0);
            $table->string('shipping_method')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('dateconfirmed')->nullable();
            $table->string('confirmed_by', 50)->nullable();
            $table->string('modified_by', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ros');
    }
};
