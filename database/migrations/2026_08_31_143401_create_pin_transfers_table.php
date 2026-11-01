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
        Schema::create('pin_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->index()->constrained('member_networks');
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->foreignId('pin_id')->index()->constrained('pins');
            $table->foreignId('product_id')->index()->constrained('products');
            $table->foreignId('varian_id')->nullable()->constrained('product_varians');
            $table->foreignId('product_package_id')->nullable()->constrained('product_packages');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('price_member', 15, 2)->default(0);
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin_transfers');
    }
};
