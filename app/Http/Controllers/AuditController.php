<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class AuditController extends Controller
{
    public function index() {
        return view('audit.scan');
    }

    public function directAudit($sku) {
    $product = Product::where('sku', $sku)->firstOrFail();
    return view('audit.form_direct', compact('product'));
}

public function submitAudit(Request $request) {
    $product = Product::findOrFail($request->product_id);
    
    // 1. Update data utama produk
    $product->update([
        'last_audited_at' => now(), // Mencatat jam saat ini secara otomatis
    ]);

    // 2. Simpan ke Riwayat Audit (History)
    \DB::table('audit_logs')->insert([
        'product_id' => $product->id,
        'audit_date' => now(),
        'notes' => $request->notes,
        'auditor_name' => auth()->user()->name ?? 'Staff',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect('/products')->with('success', 'Audit barang ' . $product->name . ' berhasil dicatat di riwayat!');
}
}