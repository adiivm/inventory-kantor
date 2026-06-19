<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ConsumableTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([[]]);
    }

    public function headings(): array
    {
        return [
            'name', 'category', 'unit', 'supplier', 'min_stock', 'current_stock',
        ];
    }
}
