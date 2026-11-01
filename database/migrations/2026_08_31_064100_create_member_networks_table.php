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
        Schema::create('member_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('referral_code')->unique();
            $table->unsignedTinyInteger('hu_index')->default(1);
            $table->string('package');
            $table->foreignId('sponsor_id')->nullable()->constrained('member_networks');
            $table->foreignId('parent_id')->nullable()->constrained('member_networks');
            $table->enum('position', ['left', 'right'])->nullable();
            $table->unsignedInteger('group')->default(0);
            $table->unsignedInteger('gen')->default(0);
            $table->unsignedInteger('level')->default(0);
            $table->mediumText('tree')->nullable();
            $table->mediumText('tree_sponsor')->nullable();
            $table->decimal('total_omzet', 15, 2)->default(0);
            $table->decimal('package_omzet', 15, 2)->default(0);
            $table->unsignedTinyInteger('rank_id')->nullable()->index();
            $table->boolean('is_ro')->default(false);
            $table->decimal('autoro_balance', 15, 2)->default(0);
            $table->timestamp('datero')->nullable();
            $table->timestamp('dateupgrade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_networks');
    }
};
