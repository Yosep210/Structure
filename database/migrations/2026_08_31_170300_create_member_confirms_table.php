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
        Schema::create('member_confirms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('downline_id')->nullable()->index()->constrained('members')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->nullable()->constrained('member_networks');
            $table->foreignId('parent_id')->nullable()->constrained('member_networks');
            $table->enum('position', ['left', 'right'])->nullable();
            $table->string('package', 20);
            $table->unsignedInteger('package_count')->default(1);
            $table->string('referral_code')->nullable();
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->unsignedSmallInteger('uniquecode')->nullable();
            $table->decimal('total_payment', 15, 2)->default(0);
            $table->foreignId('shop_order_id')->nullable()->constrained('shop_orders');
            $table->string('access')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamp('dateconfirmed')->nullable();
            $table->string('confirm_by', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_confirms');
    }
};
