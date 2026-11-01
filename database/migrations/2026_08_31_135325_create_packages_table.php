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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package', 20)->unique();
            $table->string('package_name');
            $table->unsignedInteger('package_index');
            $table->unsignedInteger('package_count')->default(1);
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('sponsor_percent', 5, 2)->default(0);
            $table->decimal('passup_percent', 5, 2)->default(0);
            $table->decimal('pairing_percent', 5, 2)->default(0);
            $table->decimal('pairing_nominal', 15, 2)->default(0);
            $table->decimal('pairing_max', 15, 2)->default(0);
            $table->decimal('pairing_point', 15, 2)->default(0);
            $table->decimal('reward_point', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_register')->default(true);
            $table->boolean('is_order')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->string('modified_by', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
