<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_ready', 'stock_repair', 'stock_broken', 'stock_disposed']);
            $table->string('condition')->default('ready')->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_ready')->default(0);
            $table->integer('stock_repair')->default(0);
            $table->integer('stock_broken')->default(0);
            $table->integer('stock_disposed')->default(0);
            $table->string('condition')->nullable()->change();
        });
    }
};
