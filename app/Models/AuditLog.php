<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    public $timestamps = false;
    protected $fillable = ['product_id', 'audit_date', 'auditor_name', 'notes', 'image_path'];
    
    protected $casts = [
        'audit_date' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}