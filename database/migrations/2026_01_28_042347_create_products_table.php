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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained();
            $table->foreignId('division_id')->constrained();
            $table->integer('stock')->default(0);
            $table->integer('stock_ready')->default(0);
            $table->integer('stock_repair')->default(0);
            $table->integer('stock_broken')->default(0);
            $table->integer('stock_disposed')->default(0);
            $table->integer('price');
            $table->date('purchase_date');

            // TAMBAHKAN BARIS INI:
            $table->string('is_active')->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
