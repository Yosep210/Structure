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
        Schema::create('member_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upgrader_id')->nullable()->index()->constrained('member_networks')->nullOnDelete();
            $table->foreignId('member_network_id')->index()->constrained('member_networks')->cascadeOnDelete();
            $table->string('package_before', 50)->nullable();
            $table->string('package_after', 50)->nullable();
            $table->string('upgrade_type', 50)->nullable();
            $table->decimal('omzet', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('point', 15, 2)->default(0);
            $table->text('pins')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_upgrades');
    }
};
