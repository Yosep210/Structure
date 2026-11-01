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
        Schema::create('member_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_network_id')->index()->constrained('member_networks')->onDelete('cascade');
            $table->foreignId('reward_config_id')->index()->constrained('reward_configs')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->decimal('point_qualified', 15, 2)->default(0);
            $table->decimal('point_left', 15, 2)->default(0);
            $table->decimal('point_right', 15, 2)->default(0);
            $table->unsignedTinyInteger('rank_id')->nullable()->index();
            $table->string('message')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('nominal_receipt', 15, 2)->default(0);
            $table->decimal('admin_fund', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->foreignId('bank_id')->nullable()->index()->constrained('banks');
            $table->string('bank_code')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->boolean('is_trip')->default(false);
            $table->boolean('claim')->default(false);
            $table->string('flip_id')->nullable(); // referensi payment gateway Flip
            $table->tinyInteger('status')->default(0)->index();
            $table->string('inquiry_status')->nullable();
            $table->timestamp('dateconfirmed')->nullable();
            $table->timestamp('dateclaimed')->nullable();
            $table->string('confirm_by', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_rewards');
    }
};
