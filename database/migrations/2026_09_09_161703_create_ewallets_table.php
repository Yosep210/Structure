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
        Schema::create('ewallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->index()->constrained('members');
            $table->string('source')->index();
            $table->unsignedBigInteger('source_id')->index();
            $table->enum('category', ['commission', 'profit'])->default('commission');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('percent', 5, 2)->default(100);
            $table->decimal('autoro', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('type', ['IN', 'OUT'])->index();
            $table->tinyInteger('status')->default(1)->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ewallets');
    }
};
