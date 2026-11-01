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
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->index()->constrained('countries')->onDelete('cascade');
            $table->foreignId('area_id')->nullable()->index()->constrained('areas')->onDelete('set null');
            $table->string('name');
            $table->string('code')->nullable()->index();
            $table->string('id_express')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
