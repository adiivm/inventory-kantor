<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'image', 'category_id', 'division_id', 
        'stock', 'price', 'condition',
        'held_by_id', 'location_id', 'usage_type', 'last_audited_at',
        'purchase_date', 'is_active', 'warranty_expiry_date', 'supplier_id'
    ];

    protected $casts = [
        'warranty_expiry_date' => 'date',
        'purchase_date' => 'date', // Pastikan kolom tanggal lain juga masuk sini
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

    protected static function boot() {
        parent::boot();

        static::saving(function ($product) {
            // Set stock = 1 untuk setiap item (1 SKU = 1 item)
            $product->stock = 1;
            
            // Default condition jika kosong
            if (empty($product->condition)) {
                $product->condition = 'ready';
            }
        });
    }

    // Di dalam class Product
    public function latestAudit()
    {
        // Mengambil 1 data AuditLog terbaru berdasarkan kolom audit_date
        return $this->hasOne(AuditLog::class)->latestOfMany('audit_date');
    }

    // Tambahkan ini di dalam class Product
    public function logs() {
        return $this->hasMany(ProductLog::class);
    }

    public function auditLogs()
    {
        // Mengambil history, urutkan dari yang terbaru
        return $this->hasMany(\App\Models\AuditLog::class, 'product_id')->latest();
    }
        
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getSupplierNameAttribute()
    {
        return $this->supplier->name ?? '-';
    }

    // Fungsi ini otomatis mengecek status warna: Hijau, Kuning, atau Abu-abu
    public function getWarrantyColorAttribute()
    {
        if (!$this->warranty_expiry_date) {
            return 'secondary'; // Tidak ada garansi -> Abu-abu
        }

        $expiry = \Carbon\Carbon::parse($this->warranty_expiry_date)->endOfDay();
        $now = \Carbon\Carbon::now();

        if ($expiry->isPast()) {
            return 'secondary'; // Sudah lewat -> Abu-abu
        } elseif ($now->diffInDays($expiry) <= 30) {
            return 'warning text-dark'; // Sisa <= 30 hari (1 bulan) -> Kuning
        } else {
            return 'success'; // Masih panjang -> Hijau
        }
    }
}

