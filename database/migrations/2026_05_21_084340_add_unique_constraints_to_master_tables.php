<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });

        Schema::table('divisions', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('divisions', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
