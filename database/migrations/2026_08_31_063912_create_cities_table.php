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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->index()->constrained('provinces')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('code')->nullable()->index();
            $table->string('postal_code')->nullable();
            $table->string('id_express')->nullable();
            $table->string('lion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
