<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index() {
        return view('audit.scan');
    }

    public function directAudit($sku) {
    $product = Product::with(['images', 'heldBy', 'location', 'latestAudit'])->where('sku', $sku)->firstOrFail();
    $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();
    return view('audit.form_direct', compact('product', 'users'));
}

public function submitAudit(Request $request) {
    $request->validate([
        'auditor_name' => 'required',
        'notes' => 'required',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
    ]);
    
    $product = Product::findOrFail($request->product_id);
    
    // 1. Update data utama produk
    $product->update([
        'last_audited_at' => now(),
    ]);

    // 2. Handle image upload
    $imagePath = null;
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '_audit_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('audit', $filename, 'public');
        $imagePath = $filename;
    }

    // 3. Simpan ke Riwayat Audit (History) - menggunakan model
    AuditLog::create([
        'product_id' => $product->id,
        'audit_date' => now(),
        'notes' => $request->notes,
        'auditor_name' => $request->auditor_name,
        'image_path' => $imagePath,
    ]);

    // 4. Tampilkan halaman sukses
    return view('audit.success', compact('product'));
}
}