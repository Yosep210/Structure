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
        Schema::create('ongkirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_id')->index()->constrained('cities')->onDelete('cascade');
            $table->foreignId('destination_id')->index()->constrained('cities')->onDelete('cascade');
            $table->unsignedBigInteger('province_id')->nullable()->index();
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->unsignedBigInteger('district_id')->nullable()->index();
            $table->unsignedBigInteger('id_province')->nullable()->index();
            $table->unsignedBigInteger('id_city')->nullable()->index();
            $table->unsignedBigInteger('id_district')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ongkirs');
    }
};
