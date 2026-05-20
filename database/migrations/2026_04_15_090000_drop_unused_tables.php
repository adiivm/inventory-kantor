<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Drop tabel yang tidak terpakai
     */
    public function up(): void
    {
        // 1. Drop product_active (sudah deprecated, diganti dengan kolom is_active di products)
        Schema::dropIfExists('product_active');
        
        // 2. Drop sessions (SESSION_DRIVER=file di .env, jadi tidak perlu database)
        Schema::dropIfExists('sessions');
        
        // 3. Drop job tables (tidak pakai background jobs)
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('jobs');
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Tidak ada reverse - ini adalah cleanup migration permanenKalau mau restore, gunakan backup SQL
        // Command: mysql -u root db_inventory < backup_database_xxx/db_inventory_dump.sql
    }
};
