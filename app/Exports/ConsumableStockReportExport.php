<?php

namespace App\Exports;

use App\Models\ConsumableItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ConsumableStockReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ConsumableItem::with('category', 'supplier')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Kategori',
            'Stok Saat Ini',
            'Min. Stok',
            'Satuan',
            'Supplier',
            'Status',
        ];
    }

    public function map($item): array
    {
        $status = 'Aman';
        if ($item->current_stock <= 0) {
            $status = 'Habis';
        } elseif ($item->current_stock <= $item->min_stock) {
            $status = 'Menipis';
        }

        static $no = 0;
        $no++;

        return [
            $no,
            $item->name,
            $item->category?->name ?? '-',
            $item->current_stock,
            $item->min_stock,
            $item->unit,
            $item->supplier?->name ?? $item->supplier_name ?? '-',
            $status,
        ];
    }
}
