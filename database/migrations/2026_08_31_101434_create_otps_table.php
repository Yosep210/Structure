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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members');
            $table->string('email')->nullable();
            $table->string('type');
            $table->string('ip_address')->nullable();
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->string('agent')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('otp', 6);
            $table->timestamp('expiration');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
