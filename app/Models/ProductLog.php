<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLog extends Model
{
    protected $fillable = ['product_id', 'action', 'description', 'old_stock', 'new_stock', 'user_name'];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}