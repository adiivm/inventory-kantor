<?php

namespace App\Imports;

use App\Models\ConsumableCategory;
use App\Models\ConsumableItem;
use App\Models\ConsumableUnit;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ConsumableImport implements ToModel, WithHeadingRow
{
    protected $count = 0;

    public function model(array $row)
    {
        $categoryName = $row['category'] ?? $row['kategori'] ?? $row['category_name'] ?? $row['nama_kategori'] ?? null;

        $categoryId = $this->getCategoryId(trim($categoryName ?? 'Umum'));

        $this->count++;

        $unitName = $row['unit'] ?? $row['satuan'] ?? 'pcs';
        ConsumableUnit::firstOrCreate(['name' => $unitName]);

        $supplierName = $row['supplier'] ?? $row['supplier_name'] ?? $row['nama_supplier'] ?? null;
        $supplierId = null;
        if ($supplierName) {
            $supplier = Supplier::where('name', trim($supplierName))->first();
            $supplierId = $supplier?->id;
        }

        return new ConsumableItem([
            'sku' => $this->generateSku(),
            'category_id' => $categoryId,
            'name' => $row['name'] ?? $row['nama'] ?? $row['nama_barang'] ?? 'Unnamed Item',
            'unit' => $unitName,
            'supplier_id' => $supplierId,
            'min_stock' => (int) ($row['min_stock'] ?? $row['stok_minimal'] ?? $row['min'] ?? 0),
            'current_stock' => (int) ($row['current_stock'] ?? $row['stok'] ?? $row['stock'] ?? $row['stok_awal'] ?? 0),
        ]);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    private function getCategoryId(string $name): int
    {
        $category = ConsumableCategory::firstOrCreate(
            ['name' => $name],
            ['description' => 'Kategori dari import Excel']
        );

        return $category->id;
    }

    private function generateSku(): string
    {
        $last = ConsumableItem::orderBy('id', 'desc')->first();
        if (! $last || ! $last->sku) {
            return 'CSM-00000001';
        }
        $parts = explode('-', $last->sku);
        $next = ((int) end($parts)) + 1;

        return 'CSM-'.str_pad($next, 8, '0', STR_PAD_LEFT);
    }
}
