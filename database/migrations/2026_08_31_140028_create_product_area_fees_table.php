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
        Schema::create('product_area_fees', function (Blueprint $table) {
            $table->id();
            $table->morphs('feeable');
            $table->foreignId('area_id')->index()->constrained('areas');
            $table->decimal('fee', 15, 2)->default(0);
            $table->tinyInteger('status')->default(1)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_area_fees');
    }
};
