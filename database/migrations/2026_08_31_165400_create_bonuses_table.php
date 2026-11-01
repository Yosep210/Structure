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
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks')->onDelete('cascade');
            $table->string('code')->unique();
            $table->tinyInteger('type')->index();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('amount_tax', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
