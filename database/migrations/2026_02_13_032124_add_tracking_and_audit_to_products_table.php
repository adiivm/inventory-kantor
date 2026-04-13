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
        Schema::table('products', function (Blueprint $table) {
            // Kolom Tracking
            $table->enum('usage_type', ['individual', 'shared', 'consumable'])->default('individual')->after('location');
            
            // Kolom Opname (Kapan terakhir discan)
            $table->timestamp('last_audited_at')->nullable()->after('usage_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['usage_type', 'last_audited_at']);
        });
    }
};
