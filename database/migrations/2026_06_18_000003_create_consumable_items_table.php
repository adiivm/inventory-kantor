<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumable_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('consumable_categories');
            $table->string('name');
            $table->string('unit');
            $table->string('supplier_name')->nullable();
            $table->integer('min_stock')->default(0);
            $table->integer('current_stock')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_items');
    }
};
