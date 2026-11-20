<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('harga', 14, 2);
            $table->timestamps();
            $table->unique(['order_id', 'produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
