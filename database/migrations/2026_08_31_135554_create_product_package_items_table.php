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
        Schema::create('product_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->index()->constrained('product_packages')->cascadeOnDelete();
            $table->foreignId('product_id')->index()->constrained('products');
            $table->foreignId('varian_id')->nullable()->index()->constrained('product_varians');
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_package_items');
    }
};
