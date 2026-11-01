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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->nullable()->index();
            $table->unsignedTinyInteger('hu')->default(0);
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('price_hpp', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('price_member', 15, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->decimal('price_customer', 15, 2)->default(0);
            $table->decimal('sponsor_point', 15, 2)->default(0);
            $table->decimal('pairing_point', 15, 2)->default(0);
            $table->decimal('reward_point', 15, 2)->default(0);
            $table->decimal('stockist_point', 15, 2)->default(0);
            $table->decimal('reward_budget', 15, 2)->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('s3_upload')->default(false);
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
        Schema::dropIfExists('products');
    }
};
