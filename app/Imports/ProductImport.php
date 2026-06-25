<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Division;
use App\Models\HeldBy;
use App\Models\Location;
use App\Models\Product;
use App\Models\Supplier;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProductImport implements ToModel, WithHeadingRow
{
    protected $count = 0;

    public function model(array $row)
    {
        $categoryName = $row['category'] ?? $row['kategori'] ?? $row['category_name'] ?? $row['nama_kategori'] ?? null;
        $divisionName = $row['division'] ?? $row['divisi'] ?? $row['division_name'] ?? $row['nama_divisi'] ?? null;
        $heldByName = $row['held_by'] ?? $row['pemegang'] ?? $row['nama_pemegang'] ?? $row['pic'] ?? null;
        $locationName = $row['location'] ?? $row['lokasi'] ?? $row['location_name'] ?? $row['nama_lokasi'] ?? null;
        $supplierName = $row['supplier'] ?? $row['nama_supplier'] ?? null;

        $categoryId = $this->getCategoryId($categoryName ?? 'Umum');
        $divisionId = $this->getDivisionId($divisionName ?? 'Umum');
        $heldById = $this->getHeldById($heldByName ?? 'Belum Ada');
        $locationId = $this->getLocationId($locationName ?? 'Gudang Utama');
        $supplierId = $this->getSupplierId($supplierName);

        $sku = $row['sku'] ?? $this->generateSku();
        $condition = strtolower($row['condition'] ?? $row['kondisi'] ?? 'ready');
        $validConditions = ['ready', 'repair', 'broken', 'disposed'];
        if (! in_array($condition, $validConditions)) {
            $condition = 'ready';
        }

        $this->count++;

        return new Product([
            'sku' => $sku,
            'name' => $row['name'] ?? $row['nama'] ?? $row['nama_assets'] ?? $row['nama_barang'] ?? 'Unnamed Product',
            'category_id' => $categoryId,
            'division_id' => $divisionId,
            'held_by_id' => $heldById,
            'location_id' => $locationId,
            'supplier_id' => $supplierId,
            'stock' => 1,
            'condition' => $condition,
            'price' => $row['price'] ?? $row['harga'] ?? 0,
            'purchase_date' => $this->parseDate($row['purchase_date'] ?? $row['tanggal_beli'] ?? null),
            'warranty_expiry_date' => $this->parseDate($row['warranty_expiry_date'] ?? $row['garansi'] ?? null),
            'is_active' => 'active',
        ]);
    }

    public function getCount()
    {
        return $this->count;
    }

    private function getCategoryId($name)
    {
        if (empty($name)) {
            return null;
        }
        $category = Category::firstOrCreate(['name' => trim($name)]);

        return $category->id;
    }

    private function getDivisionId($name)
    {
        if (empty($name)) {
            return null;
        }
        $division = Division::firstOrCreate(['name' => trim($name)]);

        return $division->id;
    }

    private function getHeldById($name)
    {
        if (empty($name)) {
            return null;
        }
        $heldBy = HeldBy::firstOrCreate(['name' => trim($name)]);

        return $heldBy->id;
    }

    private function getLocationId($name)
    {
        if (empty($name)) {
            return null;
        }
        $location = Location::firstOrCreate(['name' => trim($name)]);

        return $location->id;
    }

    private function getSupplierId($name)
    {
        if (empty($name)) {
            return null;
        }
        $supplier = Supplier::firstOrCreate(['name' => trim($name)]);

        return $supplier->id;
    }

    private function generateSku()
    {
        $lastProduct = Product::orderBy('id', 'desc')->first();
        if (! $lastProduct) {
            return 'IVM-00000001';
        }

        $parts = explode('-', $lastProduct->sku);
        $nextNumber = ((int) end($parts)) + 1;

        return 'IVM-'.str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            if (is_numeric($date)) {
                return ExcelDate::excelToDateTimeObject($date)->format('Y-m-d');
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
