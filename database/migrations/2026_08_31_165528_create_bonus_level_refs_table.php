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
        Schema::create('bonus_level_refs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bonus_id')->index()->constrained('bonuses');
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->foreignId('downline_id')->index()->constrained('member_networks');
            $table->unsignedTinyInteger('level');
            $table->decimal('omzet', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('date')->index();
            $table->tinyInteger('status')->default(0)->index();
            $table->text('description')->nullable();
            $table->timestamp('dateomzet')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus_level_refs');
    }
};
