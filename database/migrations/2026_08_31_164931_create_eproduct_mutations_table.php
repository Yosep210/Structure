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
        Schema::create('eproduct_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks');
            $table->string('source')->index();
            $table->unsignedBigInteger('source_id')->index();
            $table->enum('category', ['product', 'shipping'])->default('product');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('type', ['IN', 'OUT'])->index();
            $table->tinyInteger('status')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eproduct_mutations');
    }
};
