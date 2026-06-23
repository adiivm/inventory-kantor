<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;

class SupplierImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use Importable;

    protected $count = 0;
    protected $errors = [];

    public function model(array $row)
    {
        $name = trim($row['name'] ?? '');
        if (empty($name)) {
            $this->errors[] = 'Baris ke-' . ($this->count + 2) . ': Nama supplier wajib diisi.';
            return null;
        }

        if (Supplier::where('name', $name)->exists()) {
            $this->errors[] = "Supplier '{$name}' sudah ada, dilewati.";
            return null;
        }

        $this->count++;

        return new Supplier([
            'name' => $name,
            'contact_person' => $row['contact_person'] ?? null,
            'phone' => $row['phone'] ?? null,
            'email' => $row['email'] ?? null,
            'address' => $row['address'] ?? null,
            'notes' => $row['notes'] ?? null,
        ]);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }
}
