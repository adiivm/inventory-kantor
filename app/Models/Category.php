<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Tambahkan baris ini:
    public function products()
    {
        // Satu Kategori memiliki banyak Produk
        return $this->hasMany(Product::class);
    }
}
