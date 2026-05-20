<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([[]]);
    }

    public function headings(): array
    {
        return [
            'sku', 'name', 'category', 'division', 'held_by', 'location',
            'supplier', 'price', 'condition', 'purchase_date', 'warranty_expiry_date'
        ];
    }
}