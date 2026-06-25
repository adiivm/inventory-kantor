<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'image', 'category_id', 'division_id',
        'stock', 'price', 'condition',
        'held_by_id', 'location_id', 'usage_type', 'last_audited_at',
        'purchase_date', 'is_active', 'warranty_expiry_date', 'supplier_id',
    ];

    protected $casts = [
        'warranty_expiry_date' => 'date',
        'purchase_date' => 'date',
    ];

    public function category()
    {
        // Barang ini MILIK sebuah Kategori
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    // Pastikan juga Relasi didefinisikan agar tampilan Asset lancar
    public function heldBy()
    {
        // Pastikan foreign key-nya benar (held_by_id)
        return $this->belongsTo(HeldBy::class, 'held_by_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    // Di dalam class Product
    public function latestAudit()
    {
        // Mengambil 1 data AuditLog terbaru berdasarkan kolom audit_date
        return $this->hasOne(AuditLog::class)->latestOfMany('audit_date');
    }

    // Tambahkan ini di dalam class Product
    public function logs()
    {
        return $this->hasMany(ProductLog::class);
    }

    public function auditLogs()
    {
        // Mengambil history, urutkan dari yang terbaru
        return $this->hasMany(AuditLog::class, 'product_id')->latest();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 'active');
    }

    public function scopeNotActive($query)
    {
        return $query->where('is_active', '!=', 'active');
    }

    public function scopeWarrantyCritical($query)
    {
        return $query->whereNotNull('warranty_expiry_date')
            ->whereDate('warranty_expiry_date', '>=', now())
            ->whereDate('warranty_expiry_date', '<=', now()->addDays(30));
    }

    public function scopeWarrantyExpired($query)
    {
        return $query->whereNotNull('warranty_expiry_date')
            ->whereDate('warranty_expiry_date', '<', now());
    }

    public function scopeCondition($query, $condition)
    {
        return $query->where('condition', $condition);
    }

    public function scopeStockLow($query, $threshold = 5)
    {
        return $query->where('stock', '<=', $threshold)->where('stock', '>', 0);
    }

    public function getSupplierNameAttribute()
    {
        return $this->supplier->name ?? '-';
    }

    // Fungsi ini otomatis mengecek status warna: Hijau, Kuning, atau Abu-abu
    public function getWarrantyColorAttribute()
    {
        if (! $this->warranty_expiry_date) {
            return 'secondary'; // Tidak ada garansi -> Abu-abu
        }

        $expiry = Carbon::parse($this->warranty_expiry_date)->endOfDay();
        $now = Carbon::now();

        if ($expiry->isPast()) {
            return 'danger'; // Sudah lewat -> Merah
        } elseif ($now->diffInDays($expiry) <= 30) {
            return 'warning text-dark'; // Sisa <= 30 hari (1 bulan) -> Kuning
        } else {
            return 'success'; // Masih panjang -> Hijau
        }
    }
}
