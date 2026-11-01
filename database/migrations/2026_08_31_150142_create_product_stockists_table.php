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
        Schema::create('product_stockists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index()->constrained('products');
            $table->foreignId('stockist_id')->index()->constrained('member_networks'); // HU yang berstatus stockist
            $table->decimal('price', 15, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stockists');
    }
};
