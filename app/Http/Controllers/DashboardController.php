<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;  // <-- Pastikan ini ada
use App\Models\User;     // <-- Pastikan ini ada
use App\Models\Category; // <-- Pastikan ini ada
use Illuminate\Support\Facades\Auth; // <-- Untuk Auth::user()

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total semua UNIT barang yang ada (menggunakan kolom stock)
        // Hanya hitung yang statusnya 'active' agar barang yang sudah terjual/buang tidak masuk aset gudang
        $totalBarang = Product::where('is_active', 'active')->sum('stock');

        $totalUser = User::count();
        
        // 2. Menghitung JUMLAH JENIS BARANG yang stok totalnya (kolom stock) menipis (<= 5)
        $stokMenipis = Product::where('is_active', 'active')
            ->where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->count();

        // 3. Total UNIT yang SIAP PAKAI (Ready)
        $barangReady = Product::where('is_active', 'active')->where('condition', 'ready')->count();

        // 4. Total UNIT yang SEDANG SERVIS (Repair)
        $barangServis = Product::where('is_active', 'active')->where('condition', 'repair')->count();
        
        // 5. Total UNIT yang RUSAK (Broken)
        $barangRusak = Product::where('is_active', 'active')->where('condition', 'broken')->count();
        
        // 6. Menghitung barang yang benar-benar HABIS (Total stoknya 0)
        $stokHabis = Product::where('is_active', 'active')
            ->where('stock', '<=', 0)
            ->count();

        // 7. Total asset yang di-ARCHIVE / TRASHED (non-active)
        $barangArchive = Product::where('is_active', '!=', 'active')->count();

        // 8. Total NILAI ASSET (Harga x Stok untuk semua barang aktif)
        $totalNilaiAsset = Product::where('is_active', 'active')
            ->selectRaw('SUM(price * stock) as total')
            ->value('total') ?? 0;

        // 9. Data Grafik (Hanya barang aktif)
        $chartData = \App\Models\Category::withCount(['products' => function($query) {
            $query->where('is_active', 'active');
        }])->get();

        $labels = $chartData->pluck('name'); 
        $data   = $chartData->pluck('products_count'); 

        // 10. Garansi Kritis (<= 30 hari)
        $garansiKritis = Product::where('is_active', 'active')
            ->where('warranty_expiry_date', '>=', now())
            ->where('warranty_expiry_date', '<=', now()->addDays(30))
            ->count();

        // 11. Garansi Expired
        $garansiExpired = Product::where('is_active', 'active')
            ->where('warranty_expiry_date', '<', now())
            ->count();

        return view('dashboard', compact(
            'totalBarang', 'totalUser', 'stokMenipis', 'barangReady', 
            'barangServis', 'barangRusak', 'stokHabis', 'barangArchive', 
            'totalNilaiAsset', 'labels', 'data', 'garansiKritis', 'garansiExpired'
        ));
    }
}
