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
        Schema::create('stockists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->boolean('as_stockist')->default(false)->index();
            $table->boolean('as_stockist_pusat')->default(false);
            $table->string('stockist_name')->nullable();
            $table->foreignId('stockist_province')->nullable()->index()->constrained('provinces');
            $table->foreignId('stockist_city')->nullable()->index()->constrained('cities');
            $table->foreignId('stockist_district')->nullable()->index()->constrained('districts');
            $table->string('stockist_village')->nullable();
            $table->text('stockist_address')->nullable();
            $table->foreignId('to_stockist')->nullable()->index()->constrained('member_networks');
            $table->timestamp('datestockist')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockists');
    }
};
