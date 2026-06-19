<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("stock_transactions", function (Blueprint $table) {
            $table->string("status", 20)->default("approved")->after("notes");
            $table->foreignId("approved_by")->nullable()->constrained("users")->nullOnDelete()->after("status");
        });
    }

    public function down(): void
    {
        Schema::table("stock_transactions", function (Blueprint $table) {
            $table->dropForeign(["approved_by"]);
            $table->dropColumn(["status", "approved_by"]);
        });
    }
};
