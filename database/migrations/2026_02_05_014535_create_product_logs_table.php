<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('product_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('action'); // Contoh: 'CREATE', 'UPDATE', 'DELETE'
            $table->text('description'); // Contoh: 'Menambah stok ready sebanyak 5'
            $table->integer('old_stock')->nullable();
            $table->integer('new_stock')->nullable();
            $table->string('user_name')->nullable(); // Siapa yang melakukan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_logs');
    }
};
