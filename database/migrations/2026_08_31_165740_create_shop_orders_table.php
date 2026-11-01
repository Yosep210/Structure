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
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->foreignId('member_network_id')->nullable()->index()->constrained('member_networks');
            $table->foreignId('stockist_id')->nullable()->index()->constrained('member_networks');
            $table->string('type_order')->index();
            $table->json('products')->nullable();
            $table->json('meta')->nullable();
            $table->tinyInteger('status')->default(0)->index();
            $table->decimal('point_sponsor', 15, 2)->default(0);
            $table->decimal('point_pairing', 15, 2)->default(0);
            $table->decimal('point_reward', 15, 2)->default(0);
            $table->decimal('point_stockist', 15, 2)->default(0);
            $table->decimal('total_bv', 15, 2)->default(0);
            $table->decimal('total_omzet', 15, 2)->default(0);
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->unsignedSmallInteger('unique')->nullable();
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('shipping_discount', 15, 2)->default(0);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('handling_fee', 15, 2)->default(0);
            $table->decimal('insurance_fee', 15, 2)->default(0);
            $table->decimal('additional_cost', 15, 2)->default(0);
            $table->boolean('autoro')->default(false);
            $table->decimal('total_checkout', 15, 2)->default(0);
            $table->decimal('total_payment', 15, 2)->default(0);
            $table->decimal('payment_remain', 15, 2)->default(0);
            $table->string('voucher')->nullable();
            $table->decimal('saldo_eproduct', 15, 2)->default(0);
            $table->decimal('saldo_eshipping', 15, 2)->default(0);
            $table->decimal('saldo_eshipping_subsidy', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_shipping_method')->nullable();
            $table->string('payment_shipping_status')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('account_number')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->foreignId('province_id')->nullable()->index()->constrained('provinces');
            $table->foreignId('district_id')->nullable()->index()->constrained('districts');
            $table->unsignedBigInteger('subdistrict_id')->nullable()->index();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('subdistrict')->nullable();
            $table->string('village')->nullable();
            $table->text('address');
            $table->string('postcode')->nullable();
            $table->string('courier')->nullable();
            $table->string('service')->nullable();
            $table->decimal('weight', 10, 2)->default(0);
            $table->string('resi')->nullable();
            $table->string('pin_transfer')->nullable();
            $table->text('description')->nullable();
            $table->string('created_by', 50)->nullable();
            $table->string('confirmed_by', 50)->nullable();
            $table->string('modified_by', 50)->nullable();
            $table->timestamp('dateconfirmed')->nullable();
            $table->timestamp('datestockistconfirmed')->nullable();
            $table->timestamp('dateexpired')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};
