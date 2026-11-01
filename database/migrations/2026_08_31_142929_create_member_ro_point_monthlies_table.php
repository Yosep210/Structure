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
        Schema::create('member_ro_point_monthlies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('total_bv', 15, 2)->default(0);
            $table->decimal('total_bv_left', 15, 2)->default(0);
            $table->decimal('total_bv_right', 15, 2)->default(0);
            $table->decimal('kurs_bv_point', 8, 4)->default(1); // snapshot nilai tukar bulan itu
            $table->decimal('point', 15, 2)->default(0);
            $table->decimal('point_left', 15, 2)->default(0);
            $table->decimal('point_right', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0)->index();
            $table->text('description')->nullable();
            $table->date('date')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_ro_point_monthlies');
    }
};
