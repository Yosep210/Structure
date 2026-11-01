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
        Schema::create('auto_ros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->string('source')->index(); // polymorphic: 'bonus', 'shop', dll
            $table->unsignedBigInteger('source_id')->index();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('percent', 5, 2)->default(0); // % potongan auto RO
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('type', ['IN', 'OUT'])->index(); // IN=masuk, OUT=keluar
            $table->tinyInteger('status')->default(1)->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_ros');
    }
};
