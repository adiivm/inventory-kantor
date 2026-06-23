<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplierTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([[]]);
    }

    public function headings(): array
    {
        return [
            'name', 'contact_person', 'phone', 'email', 'address', 'notes',
        ];
    }
}
