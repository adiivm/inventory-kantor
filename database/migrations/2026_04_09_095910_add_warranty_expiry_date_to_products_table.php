<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Menambahkan kolom tanggal garansi, posisinya ditaruh setelah purchase_date (opsional)
            $table->date('warranty_expiry_date')->nullable()->after('purchase_date');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('warranty_expiry_date');
        });
    }
};
