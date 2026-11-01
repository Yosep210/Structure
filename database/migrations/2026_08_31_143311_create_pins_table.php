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
        Schema::create('pins', function (Blueprint $table) {
            $table->id();
            $table->string('id_order_pin')->nullable();
            $table->string('id_pin')->unique();
            $table->foreignId('member_network_id')->index()->constrained('member_networks'); // pemegang PIN saat ini
            $table->foreignId('owner_id')->index()->constrained('member_networks'); // pemilik awal PIN
            $table->foreignId('registrar_id')->nullable()->constrained('member_networks'); // HU sponsor yg input PIN
            $table->foreignId('registered_network_id')->nullable()->constrained('member_networks'); // HU baru yg didaftarkan
            $table->foreignId('product_id')->index()->constrained('products');
            $table->foreignId('varian_id')->nullable()->constrained('product_varians');
            $table->string('type')->nullable();
            $table->decimal('bv', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->tinyInteger('status')->default(0)->index(); // 0=available, 1=reserved, 2=used
            $table->boolean('used')->default(false)->index();
            $table->text('description')->nullable();
            $table->timestamp('dateexpired')->nullable();
            $table->timestamp('dateused')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pins');
    }
};
