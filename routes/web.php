<?php

use App\Http\Controllers\ProductController;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditController;
use App\Models\Held_by;
use App\Models\Location;

// Jalur yang bisa diakses tanpa login
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login_proses']);
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Route saat QR di-scan oleh kamera HP
Route::get('/audit/direct/{sku}', [AuditController::class, 'directAudit'])->name('audit.direct');
Route::post('/audit/submit', [AuditController::class, 'submitAudit'])->name('audit.submit');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /** --- MANAJEMEN PRODUK (STRUKTUR TETAP DIJAGA) --- **/
    Route::get('/products', [ProductController::class, 'index'])->name('product.index');

    // 1. Rute Statis (Tanpa {id}) - Tetap di Atas
    Route::get('/product/trash', [ProductController::class, 'trash'])->name('product.trash');
    Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
    Route::get('/product/pdf', [ProductController::class, 'exportPdf'])->name('product.pdf');
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export-excel', [\App\Http\Controllers\ReportController::class, 'exportExcel'])->name('reports.export_excel');
    Route::post('/product/store', [ProductController::class, 'store'])->name('product.store');

    // 2. Rute Dinamis (Yang pakai {id})
    Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
    Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
    Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/product/delete/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
    Route::get('/product/{id}/all-logs', [ProductController::class, 'getAllLogs'])->name('product.allLogs');
    Route::get('/product/api/{id}', [App\Http\Controllers\ProductController::class, 'getApiData']);
    Route::post('/product/archive/{id}', [ProductController::class, 'archive'])->name('product.archive');
    Route::post('/product/restore/{id}', [ProductController::class, 'restore'])->name('product.restore');
    Route::post('/product/image-primary/{id}', [ProductController::class, 'setPrimaryImage'])->name('product.image.primary');
    Route::delete('/product/image-delete/{id}', [ProductController::class, 'deleteImage'])->name('product.image.delete');


    /** --- MANAJEMEN USER (SESUAI BLADE INDEX USER) --- **/
    // Menampilkan daftar user
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    // Simpan user baru
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    // Update data & password (menggunakan PUT sesuai modal edit di blade)
    Route::put('/users/update/{id}', [UserController::class, 'update'])->name('users.update');
    // Hapus user
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    // Opsional: Jika butuh halaman edit terpisah (tapi di blade mas bro sudah pakai modal)
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');


    /** --- API & DROP-DOWN OTOMATIS --- **/
    Route::post('/api/categories', [CategoryController::class, 'store']);
    Route::post('/api/divisions', [DivisionController::class, 'store']);

    // Route untuk simpan Pemegang Baru via AJAX
    Route::post('/api/held_bies', function (Request $request) {
        $request->validate(['name' => 'required|unique:held_bies,name'], ['name.unique' => 'Nama pemegang ini sudah ada!']);
        $data = Held_by::create(['name' => $request->name]);
        return response()->json($data);
    });

    // Route untuk simpan Lokasi Baru via AJAX
    Route::post('/api/locations', function (Request $request) {
        $request->validate(['name' => 'required|unique:locations,name'], ['name.unique' => 'Nama Lokasi ini sudah ada!']);
        $data = Location::create(['name' => $request->name]);
        return response()->json($data);
    });
    

    /** --- PROFILE --- **/
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

});