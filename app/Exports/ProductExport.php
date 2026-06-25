<?php

namespace App\Exports;

use App\Models\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{
    protected $columns;

    protected $filters;

    public function __construct(array $columns, array $filters = [])
    {
        $this->columns = $columns;
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Product::with(['category', 'division', 'location', 'heldBy', 'latestAudit', 'supplier']);

        if (empty($this->filters['include_inactive'])) {
            $query->active();
        } elseif ($this->filters['include_inactive'] == '2') {
            $query->notActive();
        }

        if (! empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (! empty($this->filters['division_id'])) {
            $query->where('division_id', $this->filters['division_id']);
        }

        if (! empty($this->filters['location_id'])) {
            $query->where('location_id', $this->filters['location_id']);
        }

        if (! empty($this->filters['condition'])) {
            $query->where('condition', $this->filters['condition']);
        }

        if (! empty($this->filters['purchase_date_start'])) {
            $query->whereDate('purchase_date', '>=', $this->filters['purchase_date_start']);
        }

        if (! empty($this->filters['purchase_date_end'])) {
            $query->whereDate('purchase_date', '<=', $this->filters['purchase_date_end']);
        }

        return $query;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        $labels = [
            'sku' => 'SKU',
            'name' => 'Name',
            'category_id' => 'Category',
            'division_id' => 'Division',
            'location_id' => 'Location',
            'held_by_id' => 'Held By',
            'price' => 'Price',
            'condition' => 'Condition',
            'is_active' => 'Status',
            'supplier_id' => 'Supplier',
            'purchase_date' => 'Purchase Date',
            'warranty_expiry_date' => 'Warranty Expiry',
            'audit_date' => 'Last Audit Date',
            'auditor_name' => 'Last Auditor',
            'audit_notes' => 'Audit Notes',
        ];

        $headers = [];
        foreach ($this->columns as $col) {
            $headers[] = $labels[$col] ?? ucwords(str_replace(['_id', '_'], ['', ' '], $col));
        }

        return $headers;
    }

    public function map($product): array
    {
        $data = [];
        foreach ($this->columns as $column) {
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
                $data[] = $product->purchase_date ? Carbon::parse($product->purchase_date)->format('d/m/Y') : '-';
            } elseif ($column == 'warranty_expiry_date') {
                $data[] = $product->warranty_expiry_date ? Carbon::parse($product->warranty_expiry_date)->format('d/m/Y') : '-';
            } elseif ($column == 'audit_date') {
                $data[] = $product->latestAudit ? Carbon::parse($product->latestAudit->audit_date)->format('d/m/Y H:i') : '-';
            } elseif ($column == 'auditor_name') {
                $data[] = $product->latestAudit->auditor_name ?? '-';
            } elseif ($column == 'audit_notes') {
                $data[] = $product->latestAudit->notes ?? '-';
            } elseif ($column == 'condition') {
                $cond = strtolower($product->condition ?? 'ready');
                $labels = ['ready' => 'Ready', 'repair' => 'Servis', 'broken' => 'Rusak', 'disposed' => 'Dibuang'];
                $data[] = $labels[$cond] ?? $cond;
            } elseif ($column == 'is_active') {
                $status = strtolower($product->is_active ?? 'active');
                $labels = ['active' => 'Active', 'archive' => 'Archived', 'jual' => 'Sold', 'destroy' => 'Destroyed'];
                $data[] = $labels[$status] ?? $status;
            } else {
                $data[] = $product->$column ?? '';
            }
        }

        return $data;
    }
}
