<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromCollection, WithHeadings, WithMapping
{
    protected $columns;

    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    // 1. Ambil Data beserta relasinya
    public function collection()
    {
        // Panggil relasi agar performa tetap cepat (N+1 safe)
        return Product::with(['category', 'division', 'location', 'heldBy', 'latestAudit', 'supplier'])->get();
    }

    // 2. Tentukan Header Kolom Excel
    public function headings(): array
    {
        $headers = [];
        foreach ($this->columns as $col) {
            // Ubah format tulisan agar rapi (contoh: purchase_date -> Purchase Date)
            $label = str_replace(['_id', '_'], ['', ' '], $col);
            $headers[] = ucwords($label); 
        }
        return $headers;
    }

    // 3. Petakan Data ke Kolom
    public function map($product): array
    {
        $data = [];
        foreach ($this->columns as $column) {
            // Logika khusus untuk relasi
            if ($column == 'category_id') {
                $data[] = $product->category->name ?? '-';
            } elseif ($column == 'division_id') {
                $data[] = $product->division->name ?? '-';
            } elseif ($column == 'location_id') {
                $data[] = $product->location->name ?? '-';
            } elseif ($column == 'held_by_id') {
                $data[] = $product->heldBy->name ?? '-';
            } elseif ($column == 'supplier_id') {
                $data[] = $product->supplier->name ?? '-';
            } elseif ($column == 'purchase_date') {
                $data[] = $product->purchase_date ? \Carbon\Carbon::parse($product->purchase_date)->format('d/m/Y H:i') : '-';
            } elseif ($column == 'warranty_expiry_date') {
                $data[] = $product->warranty_expiry_date ? \Carbon\Carbon::parse($product->warranty_expiry_date)->format('d/m/Y H:i') : '-';
            } elseif ($column == 'audit_date') {
                $data[] = $product->latestAudit ? \Carbon\Carbon::parse($product->latestAudit->audit_date)->format('d/m/Y H:i') : '-';
            } elseif ($column == 'auditor_name') {
                $data[] = $product->latestAudit->auditor_name ?? '-';
            } elseif ($column == 'audit_notes') {
                $data[] = $product->latestAudit->notes ?? '-';
            } elseif ($column == 'condition') {
                $cond = strtolower($product->condition ?? 'ready');
                $labels = ['ready' => 'Ready', 'repair' => 'Servis', 'broken' => 'Rusak', 'disposed' => 'Dibuang'];
                $data[] = $labels[$cond] ?? $cond;
            } else {
                // Untuk kolom biasa (SKU, Name, Price, dll)
                $data[] = $product->$column ?? '';
            }
        }
        return $data;
    }
}