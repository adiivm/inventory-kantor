<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'image', 'category_id', 'division_id', 
        'stock', 'stock_ready', 'stock_repair', 'stock_broken', 
        'stock_disposed', 'price',
        'held_by_id', 'location_id', 'usage_type', 'last_audited_at',
        'purchase_date', 'is_active', 'warranty_expiry_date',
        'reason'  // ✅ Tambahkan ini
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
        return $this->belongsTo(Held_By::class, 'held_by_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    protected static function boot() {
        parent::boot();

        static::saving(function ($product) {
            // 1. Hitung total stok (Logika lama Mas Bro)
            $product->stock = (int)$product->stock_ready + 
                            (int)$product->stock_repair + 
                            (int)$product->stock_broken + 
                            (int)$product->stock_disposed;

            // 2. Logika Update Kolom 'condition' Otomatis
            // Kita beri prioritas: Jika ada stok ready, maka status Ready. 
            // Jika ready kosong tapi ada repair, maka status Repair, dst.
            if ($product->stock_ready > 0) {
                $product->condition = 'Ready';
            } elseif ($product->stock_repair > 0) {
                $product->condition = 'Repair';
            } elseif ($product->stock_broken > 0) {
                $product->condition = 'Broken';
            } elseif ($product->stock_disposed > 0) {
                $product->condition = 'Disposed';
            } else {
                $product->condition = 'Ready'; // Default jika semua nol
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

