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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->index()->constrained('members');
            $table->string('type')->nullable();
            $table->foreignId('bank_id')->nullable()->index()->constrained('banks');
            $table->string('bank_type')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable(); // nomor rekening
            $table->string('bank_account_name')->nullable(); // nama pemilik rekening
            $table->decimal('nominal', 15, 2)->default(0);
            $table->decimal('nominal_receipt', 15, 2)->default(0); // nominal - tax - autoro - admin_fund
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('auto_ro', 15, 2)->default(0);
            $table->decimal('admin_fund', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0)->index();
            $table->string('flip_id')->nullable(); // referensi payment gateway
            $table->string('linkita_inquiry')->nullable();
            $table->string('inquiry_status')->nullable();
            $table->string('linkita_pay')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamp('dateconfirmed')->nullable();
            $table->string('confirm_by', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
