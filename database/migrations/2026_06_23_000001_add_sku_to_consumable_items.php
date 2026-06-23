<?php

use App\Models\ConsumableItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumable_items', function (Blueprint $table) {
            $table->string('sku', 20)->nullable()->unique()->after('id');
        });

        $rows = ConsumableItem::withTrashed()->whereNull('sku')->get();
        foreach ($rows as $item) {
            $item->sku = 'CSM-' . str_pad($item->id, 8, '0', STR_PAD_LEFT);
            $item->saveQuietly();
        }

        Schema::table('consumable_items', function (Blueprint $table) {
            $table->string('sku', 20)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('consumable_items', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};
