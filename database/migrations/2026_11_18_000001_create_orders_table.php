<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customer')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('noresi')->nullable();
            $table->string('kurir')->nullable();
            $table->string('layanan_ongkir')->nullable();
            $table->unsignedInteger('biaya_ongkir')->nullable();
            $table->string('estimasi_ongkir')->nullable();
            $table->unsignedInteger('total_berat')->default(0);
            $table->decimal('total_harga', 14, 2)->default(0);
            $table->text('alamat')->nullable();
            $table->string('pos', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
