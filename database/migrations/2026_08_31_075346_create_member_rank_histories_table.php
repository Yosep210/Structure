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
        Schema::create('member_rank_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->unsignedTinyInteger('rank_id')->nullable()->index();
            $table->unsignedInteger('downline_count')->default(0);
            $table->unsignedInteger('point_left')->default(0);
            $table->unsignedInteger('point_right')->default(0);
            $table->tinyInteger('status')->default(1)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_rank_histories');
    }
};
