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
        Schema::create('member_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->string('package', 20)->nullable();
            $table->string('type')->nullable();
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('point', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_points');
    }
};
