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
        Schema::create('ewallet_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('id_transfer')->unique();
            $table->foreignId('sender_network_id')->index()->constrained('member_networks');
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('nominal_receipt', 15, 2)->default(0); // nominal - admin_fund
            $table->decimal('admin_fund', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0)->index();
            $table->text('description')->nullable();
            $table->string('created_by', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ewallet_transfers');
    }
};
