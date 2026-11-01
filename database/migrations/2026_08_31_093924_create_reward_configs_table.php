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
        Schema::create('reward_configs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('reward')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('point', 15, 2)->default(0);
            $table->json('packages')->nullable();
            $table->foreignId('rank_id')->nullable()->index();
            $table->string('message')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_lifetime')->default(false);
            $table->boolean('is_trip')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_configs');
    }
};
