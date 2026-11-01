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
        Schema::create('product_stockist_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number')->unique();
            $table->foreignId('stockist_id')->index()->constrained('member_networks');
            $table->foreignId('product_id')->index()->constrained('products');
            $table->foreignId('varian_id')->nullable()->constrained('product_varians');
            $table->unsignedInteger('recorded_qty')->default(0);
            $table->unsignedInteger('actual_qty')->default(0);
            $table->integer('difference_qty')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('type', ['IN', 'OUT'])->index();
            $table->text('memo')->nullable();
            $table->text('description')->nullable();
            $table->date('date_trans')->index();
            $table->string('created_by', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stockist_opnames');
    }
};
