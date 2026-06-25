<?php

namespace App\Http\Controllers;

use App\Models\Category;  // <-- Pastikan ini ada
use App\Models\Product;     // <-- Pastikan ini ada
use App\Models\User; // <-- Pastikan ini ada
use Illuminate\Support\Facades\Auth; // <-- Untuk Auth::user()

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total semua UNIT barang yang ada (menggunakan kolom stock)
        // Hanya hitung yang statusnya 'active' agar barang yang sudah terjual/buang tidak masuk aset gudang
        $totalBarang = Product::active()->sum('stock');

        $totalUser = User::count();

        // 2. Menghitung JUMLAH JENIS BARANG yang stok totalnya (kolom stock) menipis (<= 5)
        $stokMenipis = Product::active()->stockLow()->count();

        // 3. Total UNIT yang SIAP PAKAI (Ready)
        $barangReady = Product::active()->condition('ready')->count();

        // 4. Total UNIT yang SEDANG SERVIS (Repair)
        $barangServis = Product::active()->condition('repair')->count();

        // 5. Total UNIT yang RUSAK (Broken)
        $barangRusak = Product::active()->condition('broken')->count();

        // 6. Menghitung barang yang benar-benar HABIS (Total stoknya 0)
        $stokHabis = Product::active()->where('stock', '<=', 0)->count();

        // 7. Total asset yang di-ARCHIVE / TRASHED (non-active)
        $barangArchive = Product::notActive()->count();

        // 8. Total NILAI ASSET (Harga x Stok untuk semua barang aktif)
        $totalNilaiAsset = Product::active()
            ->selectRaw('SUM(price * stock) as total')
            ->value('total') ?? 0;

        // 9. Data Grafik (Hanya barang aktif)
        $chartData = Category::withCount(['products' => function ($query) {
            $query->active();
        }])->get();

        $labels = $chartData->pluck('name');
        $data = $chartData->pluck('products_count');

        // 10. Garansi Kritis (<= 30 hari)
        $garansiKritis = Product::active()->warrantyCritical()->count();

        // 11. Garansi Expired
        $garansiExpired = Product::active()->warrantyExpired()->count();

        return view('dashboard', compact(
            'totalBarang', 'totalUser', 'stokMenipis', 'barangReady',
            'barangServis', 'barangRusak', 'stokHabis', 'barangArchive',
            'totalNilaiAsset', 'labels', 'data', 'garansiKritis', 'garansiExpired'
        ));
    }
}
