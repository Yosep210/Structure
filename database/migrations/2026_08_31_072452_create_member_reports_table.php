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
        Schema::create('member_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->unique()->constrained('member_networks');
            $table->decimal('pairing_left', 15, 2)->default(0);
            $table->decimal('pairing_right', 15, 2)->default(0);
            $table->decimal('pairing_qualified', 15, 2)->default(0);
            $table->decimal('reward_left', 15, 2)->default(0);
            $table->decimal('reward_right', 15, 2)->default(0);
            $table->decimal('total_omzet', 15, 2)->default(0);
            $table->decimal('total_ro_omzet', 15, 2)->default(0);
            $table->decimal('total_ro_point', 15, 2)->default(0);
            $table->decimal('last_ro_omzet', 15, 2)->default(0);
            $table->decimal('last_ro_point', 15, 2)->default(0);
            $table->timestamp('last_ro_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_reports');
    }
};
