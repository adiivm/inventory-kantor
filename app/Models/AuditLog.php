<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    // Tentukan nama tabelnya secara manual (karena jamak)
    protected $table = 'audit_logs';

    // Izinkan kolom-kolom ini diisi secara massal
    protected $fillable = [
        'product_id',
        'audit_date',
        'notes',
        'auditor_name'
    ];

    // Relasi balik ke Produk (Setiap log punya 1 produk)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}