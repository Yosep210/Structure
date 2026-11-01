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
        Schema::create('product_packages', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->nullable()->index();
            $table->unsignedTinyInteger('hu')->default(0);
            $table->enum('type_price', ['flat', 'total'])->default('flat');
            $table->unsignedInteger('total_item')->default(0);
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('sponsor_point', 15, 2)->default(0);
            $table->decimal('pairing_point', 15, 2)->default(0);
            $table->decimal('stockist_point', 15, 2)->default(0);
            $table->decimal('reward_point', 15, 2)->default(0);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('show_order')->default(true);
            $table->tinyInteger('status')->default(1)->index();
            $table->string('created_by', 50)->nullable();
            $table->string('modified_by', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_packages');
    }
};
