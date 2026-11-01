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
        Schema::create('product_stock_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->string('source')->index();
            $table->unsignedBigInteger('source_id')->index();
            $table->unsignedBigInteger('detail_id')->nullable();
            $table->foreignId('product_id')->index()->constrained('products');
            $table->foreignId('varian_id')->nullable()->index()->constrained('product_varians');
            $table->unsignedInteger('qty')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->text('description')->nullable();
            $table->timestamp('dateexpired')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_ins');
    }
};
