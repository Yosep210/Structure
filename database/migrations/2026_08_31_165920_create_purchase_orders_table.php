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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->foreignId('supplier_id')->index()->constrained('suppliers');
            $table->string('ref_number_order', 30)->nullable();
            $table->string('ref_number_delivery', 100)->nullable();
            $table->json('products')->nullable();
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->unsignedSmallInteger('unique')->nullable();
            $table->decimal('pph', 15, 2)->default(0);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->decimal('total_payment', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0)->index();
            $table->text('memo')->nullable();
            $table->text('description')->nullable();
            $table->date('trans_date');
            $table->date('due_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamp('dateexpired')->nullable();
            $table->timestamp('dateconfirmed')->nullable();
            $table->string('created_by', 50)->nullable();
            $table->string('modified_by', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
