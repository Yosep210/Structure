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
        Schema::create('member_omzets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->string('pack_omzet', 20)->nullable();
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('sponsor', 15, 2)->default(0);
            $table->decimal('pairing', 15, 2)->default(0);
            $table->decimal('reward', 15, 2)->default(0);
            $table->decimal('stockist', 15, 2)->default(0);
            $table->decimal('ro', 15, 2)->default(0);
            $table->decimal('omzet', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0)->index();
            $table->text('description')->nullable();
            $table->date('date')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_omzets');
    }
};
