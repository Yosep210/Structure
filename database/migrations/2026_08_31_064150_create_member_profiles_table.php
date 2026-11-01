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
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->unique()->constrained('members')->cascadeOnDelete();
            $table->date('birthdate')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->tinyInteger('bank_status')->default(0);
            $table->string('id_card_number')->nullable();
            $table->string('id_card_img')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('image')->nullable();
            $table->string('qr_code_img')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('province_id')->nullable()->constrained('provinces');
            $table->foreignId('city_id')->nullable()->index()->constrained('cities')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->index()->constrained('districts')->onDelete('set null');
            $table->string('village')->nullable();
            $table->text('address')->nullable();
            $table->string('password_security')->nullable();
            $table->string('password_transaction')->nullable();
            $table->boolean('agree_ethic')->default(false);
            $table->tinyInteger('wd_status')->default(1);
            $table->decimal('wd_min', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};
