<?php

use App\Exports\ConsumableStockReportExport;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConsumableCategoryController;
use App\Http\Controllers\ConsumableDashboardController;
use App\Http\Controllers\ConsumableItemController;
use App\Http\Controllers\ConsumableUnitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Models\Category;
use App\Models\ConsumableCategory;
use App\Models\ConsumableUnit;
use App\Models\DistributionHeader;
use App\Models\Division;
use App\Models\HeldBy;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return redirect('/login');
});
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
    Route::get('/product/import-template', [ProductController::class, 'downloadTemplate'])->name('product.import_template');
    Route::post('/product/import', [ProductController::class, 'importExcel'])->name('product.import');
    Route::post('/product/bulk-print-labels', [ProductController::class, 'bulkPrintLabels'])->name('product.bulk_print_labels');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export_excel');
    Route::post('/product/store', [ProductController::class, 'store'])->name('product.store');

    // 2. Rute Dinamis (Yang pakai {id})
    Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
    Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
    Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/product/delete/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
    Route::get('/product/{id}/all-logs', [ProductController::class, 'getAllLogs'])->name('product.allLogs');
    Route::get('/product/api/{id}', [ProductController::class, 'getApiData']);
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

    /** --- API & DROP-DOWN OTOMATIS --- **/
    Route::post('/api/categories', [CategoryController::class, 'store']);
    Route::put('/api/categories/{id}', [CategoryController::class, 'update']);
    Route::post('/api/divisions', [DivisionController::class, 'store']);
    Route::put('/api/divisions/{id}', [DivisionController::class, 'update']);
    Route::get('/api/divisions', [DivisionController::class, 'getAll']);

    // Route untuk simpan Pemegang Baru via AJAX
    Route::post('/api/held_bies', function (Request $request) {
        $request->validate(['name' => 'required|unique:held_bies,name'], ['name.unique' => 'Nama pemegang ini sudah ada!']);
        $data = HeldBy::create(['name' => $request->name]);

        return response()->json($data);
    });
    Route::put('/api/held_bies/{id}', function (Request $request, $id) {
        $request->validate(['name' => 'required|unique:held_bies,name,'.$id], ['name.unique' => 'Nama pemegang ini sudah ada!']);
        $item = HeldBy::findOrFail($id);
        $item->update(['name' => $request->name]);

        return response()->json(['success' => true, 'message' => 'Pemegang berhasil diupdate.', 'data' => $item]);
    });
    Route::get('/api/held_bies', function () {
        return response()->json(HeldBy::orderBy('name')->get());
    });

    // Route untuk simpan Lokasi Baru via AJAX
    Route::post('/api/locations', function (Request $request) {
        $request->validate(['name' => 'required|unique:locations,name'], ['name.unique' => 'Nama Lokasi ini sudah ada!']);
        $data = Location::create(['name' => $request->name]);

        return response()->json($data);
    });
    Route::put('/api/locations/{id}', function (Request $request, $id) {
        $request->validate(['name' => 'required|unique:locations,name,'.$id], ['name.unique' => 'Nama Lokasi ini sudah ada!']);
        $item = Location::findOrFail($id);
        $item->update(['name' => $request->name]);

        return response()->json(['success' => true, 'message' => 'Lokasi berhasil diupdate.', 'data' => $item]);
    });

    // Hapus master data via AJAX (admin only)
    Route::delete('/api/categories/{id}', [CategoryController::class, 'destroy']);
    Route::delete('/api/divisions/{id}', [DivisionController::class, 'destroy']);
    Route::delete('/api/held_bies/{id}', function ($id) {
        Gate::authorize('staff-access');
        $item = HeldBy::findOrFail($id);
        if ($item->products()->exists()) {
            return response()->json(['success' => false, 'message' => 'Pemegang tidak bisa dihapus karena masih digunakan oleh '.$item->products()->count().' produk.'], 422);
        }
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Pemegang berhasil dihapus.']);
    });
    Route::delete('/api/consumable-categories/{id}', [ConsumableCategoryController::class, 'destroy']);
    Route::put('/api/consumable-categories/{id}', [ConsumableCategoryController::class, 'update']);
    Route::delete('/api/consumable-units/{id}', [ConsumableUnitController::class, 'destroy']);
    Route::put('/api/consumable-units/{id}', [ConsumableUnitController::class, 'update']);

    Route::delete('/api/locations/{id}', function ($id) {
        Gate::authorize('staff-access');
        $item = Location::findOrFail($id);
        if ($item->products()->exists()) {
            return response()->json(['success' => false, 'message' => 'Lokasi tidak bisa dihapus karena masih digunakan oleh '.$item->products()->count().' produk.'], 422);
        }
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Lokasi berhasil dihapus.']);
    });

    /** --- MASTER DATA --- **/
    Route::get('/master-data', function () {
        return view('master_data', [
            'categories' => Category::orderBy('name')->get(),
            'divisions' => Division::orderBy('name')->get(),
            'held_bies' => HeldBy::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'consumableCategories' => ConsumableCategory::orderBy('name')->get(),
            'consumableUnits' => ConsumableUnit::orderBy('name')->get(),
        ]);
    })->name('master.data');

    /** --- PROFILE --- **/
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    /** --- CONSUMABLE STOCK MANAGEMENT --- **/
    Route::get('/consumable/categories', [ConsumableCategoryController::class, 'index'])->name('consumable.categories');
    Route::post('/consumable/categories', [ConsumableCategoryController::class, 'store']);
    Route::get('/consumable/categories/{id}/edit', [ConsumableCategoryController::class, 'edit']);
    Route::put('/consumable/categories/{id}', [ConsumableCategoryController::class, 'update']);
    Route::delete('/consumable/categories/{id}', [ConsumableCategoryController::class, 'destroy']);
    Route::get('/api/consumable-categories', [ConsumableCategoryController::class, 'getAll']);

    Route::get('/consumable/items', [ConsumableItemController::class, 'index'])->name('consumable.items');
    Route::post('/consumable/items', [ConsumableItemController::class, 'store']);
    Route::get('/consumable/items/{id}/edit', [ConsumableItemController::class, 'edit']);
    Route::get('/consumable/items/{id}/history', [ConsumableItemController::class, 'history'])->name('consumable.items.history');
    Route::put('/consumable/items/{id}', [ConsumableItemController::class, 'update']);
    Route::delete('/consumable/items/{id}', [ConsumableItemController::class, 'destroy']);
    Route::get('/api/consumable-items', [ConsumableItemController::class, 'getAll']);
    Route::post('/consumable/items/import', [ConsumableItemController::class, 'import'])->name('consumable.items.import');
    Route::get('/consumable/items/import-template', [ConsumableItemController::class, 'downloadTemplate'])->name('consumable.items.import_template');
    Route::get('/consumable/dashboard', [ConsumableDashboardController::class, 'index'])->name('consumable.dashboard');

    Route::get('/consumable/units', [ConsumableUnitController::class, 'index'])->name('consumable.units');
    Route::post('/consumable/units', [ConsumableUnitController::class, 'store']);
    Route::get('/consumable/units/{id}/edit', [ConsumableUnitController::class, 'edit']);
    Route::put('/consumable/units/{id}', [ConsumableUnitController::class, 'update']);
    Route::delete('/consumable/units/{id}', [ConsumableUnitController::class, 'destroy']);
    Route::get('/api/consumable-units', [ConsumableUnitController::class, 'getAll']);

    Route::get('/consumable/transactions', [StockTransactionController::class, 'index'])->name('consumable.transactions');
    Route::post('/consumable/transactions', [StockTransactionController::class, 'store']);
    Route::post('/consumable/transactions/{id}/approve', [StockTransactionController::class, 'approve'])->name('consumable.transactions.approve');
    Route::post('/consumable/transactions/{id}/reject', [StockTransactionController::class, 'reject'])->name('consumable.transactions.reject');

    Route::get('/consumable/distributions', [DistributionController::class, 'index'])->name('consumable.distributions');
    Route::post('/consumable/distributions', [DistributionController::class, 'store']);
    Route::post('/consumable/distributions/{id}/approve', [DistributionController::class, 'approve'])->name('consumable.distributions.approve');
    Route::post('/consumable/distributions/{id}/reject', [DistributionController::class, 'reject'])->name('consumable.distributions.reject');
    Route::post('/consumable/distributions/{id}/signature', [DistributionController::class, 'saveSignature'])->name('consumable.distributions.signature');
    Route::get('/consumable/distributions/{id}', [DistributionController::class, 'show']);
    Route::get('/consumable/distributions/{id}/print', [DistributionController::class, 'printPdf'])->name('consumable.distributions.print');
    Route::get('/consumable/reports', [ConsumableItemController::class, 'report'])->name('consumable.reports');
    Route::post('/consumable/reports/export', function () {
        return Excel::download(
            new ConsumableStockReportExport,
            'Laporan-Stok-Consumable.xlsx'
        );
    })->name('consumable.reports.export');

    Route::get('/consumable/distributions/verify/{id}', function ($id) {
        $header = DistributionHeader::with('details.consumableItem', 'division', 'approver')->findOrFail($id);

        return view('consumable.pdf_bukti', compact('header'));
    })->name('distribution.verify');

    Route::post('/notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    })->name('notifications.read');

    Route::get('/backup', function () {
        Gate::authorize('admin-only');
        return view('backup_index');
    })->name('backup.index');

    Route::get('/backup/run', function () {
        Gate::authorize('admin-only');

        $backupDir = base_path('backups');
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }

        $date = date('Ymd_His');
        $dbFile = "$backupDir/db_$date.sql";
        $imagesFile = "$backupDir/images_$date.tar.gz";

        $host = config('database.connections.pgsql.host');
        $user = config('database.connections.pgsql.username');
        $pass = config('database.connections.pgsql.password');
        $db = config('database.connections.pgsql.database');

        $cmd = "PGPASSWORD=" . escapeshellarg($pass)
            . " pg_dump -h $host -U $user $db > " . escapeshellarg($dbFile)
            . " 2>&1";

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($dbFile)) {
            return response()->json(['success' => false, 'message' => 'Backup gagal']);
        }

        if (is_dir(storage_path('app/public'))) {
            exec("tar -czf " . escapeshellarg($imagesFile)
                . " -C " . escapeshellarg(dirname(storage_path('app/public')))
                . " " . basename(storage_path('app/public')) . " 2>&1");
        }

        foreach (glob("$backupDir/db_*.sql") as $f) {
            if (time() - filemtime($f) > 7 * 86400) @unlink($f);
        }
        foreach (glob("$backupDir/images_*.tar.gz") as $f) {
            if (time() - filemtime($f) > 7 * 86400) @unlink($f);
        }

        $size = filesize($dbFile);
        $sizeMb = round($size / 1024 / 1024, 2);

        return response()->json([
            'success' => true,
            'message' => "Backup berhasil!\nDatabase: db_$date.sql (" . number_format($sizeMb, 2) . " MB)\nLokasi: $backupDir",
        ]);
    })->name('backup.run');

    Route::get('/backup/download', function () {
        Gate::authorize('admin-only');

        $host = config('database.connections.pgsql.host');
        $user = config('database.connections.pgsql.username');
        $pass = config('database.connections.pgsql.password');
        $db = config('database.connections.pgsql.database');

        $date = date('Ymd_His');
        $tmpDir = sys_get_temp_dir() . '/backup_' . $date;
        @mkdir($tmpDir, 0755, true);

        $sqlFile = "$tmpDir/db_$date.sql";
        $cmd = "PGPASSWORD=" . escapeshellarg($pass)
            . " pg_dump -h $host -U $user $db > " . escapeshellarg($sqlFile)
            . " 2>&1";
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($sqlFile)) {
            return response()->json(['success' => false, 'message' => 'Backup gagal']);
        }

        $publicPath = storage_path('app/public');
        $imagesTar = "$tmpDir/images_$date.tar.gz";
        if (is_dir($publicPath) && glob("$publicPath/*")) {
            exec("tar -czf " . escapeshellarg($imagesTar)
                . " -C " . escapeshellarg(dirname($publicPath))
                . " " . basename($publicPath) . " 2>&1");
        }

        register_shutdown_function(function () use ($tmpDir) {
            exec("rm -rf " . escapeshellarg($tmpDir) . " 2>&1");
        });

        // Zip both files together
        $zipFile = "$tmpDir/backup_$date.zip";
        exec("zip -j " . escapeshellarg($zipFile) . " " . escapeshellarg($sqlFile) . " " . escapeshellarg($imagesTar) . " 2>&1");

        if (!file_exists($zipFile)) {
            // Fallback: send sql only
            return response()->download($sqlFile, "db_$date.sql")->deleteFileAfterSend(true);
        }

        return response()->download($zipFile, "backup_$date.zip")->deleteFileAfterSend(true);
    })->name('backup.download');

    Route::get('/backup/files', function () {
        Gate::authorize('admin-only');

        $backupDir = base_path('backups');
        if (!is_dir($backupDir)) {
            return response()->json([]);
        }

        $files = [];
        foreach (glob("$backupDir/*.sql") as $f) {
            $files[] = ['name' => basename($f), 'type' => 'sql', 'size' => filesize($f), 'date' => date('d/m/Y H:i', filemtime($f))];
        }
        foreach (glob("$backupDir/*.tar.gz") as $f) {
            $files[] = ['name' => basename($f), 'type' => 'tar.gz', 'size' => filesize($f), 'date' => date('d/m/Y H:i', filemtime($f))];
        }

        return response()->json($files);
    })->name('backup.files');

    Route::post('/backup/restore', function () {
        Gate::authorize('admin-only');

        $data = json_decode(file_get_contents('php://input'), true);
        $file = $data['file'] ?? '';
        $imageFile = $data['image_file'] ?? '';

        $backupDir = base_path('backups');
        $filePath = "$backupDir/" . basename($file);

        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'File .sql tidak ditemukan.']);
        }

        $host = config('database.connections.pgsql.host');
        $user = config('database.connections.pgsql.username');
        $pass = config('database.connections.pgsql.password');
        $db = config('database.connections.pgsql.database');

        $cmd = "PGPASSWORD=" . escapeshellarg($pass)
            . " psql -h $host -U $user -d $db < " . escapeshellarg($filePath)
            . " 2>&1";

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            return response()->json(['success' => false, 'message' => 'Restore gagal: ' . implode("\n", $output)]);
        }

        // Restore images jika user memilih file .tar.gz
        if ($imageFile) {
            $imagesFilePath = "$backupDir/" . basename($imageFile);
            if (file_exists($imagesFilePath)) {
                $imagesPath = storage_path('app/public');
                exec("tar -xzf " . escapeshellarg($imagesFilePath)
                    . " -C " . escapeshellarg(dirname($imagesPath)) . " 2>&1");
            }
        } else {
            // Auto-detect matching images (backward compatibility)
            $prefix = preg_replace('/^db_|\.sql$/', '', basename($file));
            $imagesFile = "$backupDir/images_$prefix.tar.gz";
            if (file_exists($imagesFile)) {
                $imagesPath = storage_path('app/public');
                exec("tar -xzf " . escapeshellarg($imagesFile)
                    . " -C " . escapeshellarg(dirname($imagesPath)) . " 2>&1");
            }
        }

        return response()->json(['success' => true, 'message' => 'Database ' . ($imageFile ? '+ gambar ' : '') . 'berhasil direstore dari ' . basename($file)]);
    })->name('backup.restore');

    Route::post('/backup/restore/upload', function () {
        Gate::authorize('admin-only');

        $request = request();
        if (!$request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'Pilih file .sql atau .tar.gz terlebih dahulu.']);
        }

        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        $isTarGz = str_ends_with($file->getClientOriginalName(), '.tar.gz');

        if ($ext !== 'sql' && !$isTarGz) {
            return response()->json(['success' => false, 'message' => 'Hanya file .sql atau .tar.gz yang diizinkan.']);
        }

        $backupDir = base_path('backups');
        $prefix = $isTarGz ? 'images_' : 'db_';
        $fileName = $prefix . 'upload_' . date('Ymd_His') . '_' . $file->getClientOriginalName();
        $file->move($backupDir, $fileName);

        $filePath = "$backupDir/$fileName";
        $host = config('database.connections.pgsql.host');
        $user = config('database.connections.pgsql.username');
        $pass = config('database.connections.pgsql.password');
        $db = config('database.connections.pgsql.database');

        if ($isTarGz) {
            // Just store it, restore will be done later via dropdown
            return response()->json(['success' => true, 'message' => 'File gambar berhasil diupload sebagai ' . $fileName]);
        }

        $cmd = "PGPASSWORD=" . escapeshellarg($pass)
            . " psql -h $host -U $user -d $db < " . escapeshellarg($filePath)
            . " 2>&1";

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            return response()->json(['success' => false, 'message' => 'Restore gagal: ' . implode("\n", $output)]);
        }

        // Auto-restore matching images
        $prefix = preg_replace('/^db_|\.sql$/', '', $fileName);
        $imagesFile = "$backupDir/images_$prefix.tar.gz";
        if (file_exists($imagesFile)) {
            $imagesPath = storage_path('app/public');
            exec("tar -xzf " . escapeshellarg($imagesFile)
                . " -C " . escapeshellarg(dirname($imagesPath)) . " 2>&1");
        }

        return response()->json(['success' => true, 'message' => 'Database berhasil direstore dari ' . $fileName]);
    })->name('backup.restore.upload');

    Route::delete('/backup/delete', function () {
        Gate::authorize('admin-only');

        $data = json_decode(file_get_contents('php://input'), true);
        $file = $data['file'] ?? '';

        $backupDir = base_path('backups');
        $filePath = "$backupDir/" . basename($file);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        return response()->json(['success' => true]);
    })->name('backup.delete');

    Route::post('/backup/reset', function () {
        Gate::authorize('admin-only');

        // 1. Backup otomatis dulu
        $backupDir = base_path('backups');
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }

        $date = date('Ymd_His');
        $dbFile = "$backupDir/db_$date.sql";

        $host = config('database.connections.pgsql.host');
        $user = config('database.connections.pgsql.username');
        $pass = config('database.connections.pgsql.password');
        $db = config('database.connections.pgsql.database');

        $cmd = "PGPASSWORD=" . escapeshellarg($pass)
            . " pg_dump -h $host -U $user $db > " . escapeshellarg($dbFile)
            . " 2>&1";

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($dbFile)) {
            return response()->json(['success' => false, 'message' => 'Backup gagal, reset dibatalkan.']);
        }

        // Backup images
        $imagesFile = "$backupDir/images_$date.tar.gz";
        if (is_dir(storage_path('app/public'))) {
            exec("tar -czf " . escapeshellarg($imagesFile)
                . " -C " . escapeshellarg(dirname(storage_path('app/public')))
                . " " . basename(storage_path('app/public')) . " 2>&1");
        }

        // 2. Truncate all data
        DB::statement('TRUNCATE TABLE distribution_details CASCADE');
        DB::statement('TRUNCATE TABLE distribution_headers CASCADE');
        DB::statement('TRUNCATE TABLE stock_transactions CASCADE');
        DB::statement('TRUNCATE TABLE audit_logs CASCADE');
        DB::statement('TRUNCATE TABLE product_logs CASCADE');
        DB::statement('TRUNCATE TABLE product_images CASCADE');
        DB::statement('TRUNCATE TABLE products CASCADE');
        DB::statement('TRUNCATE TABLE consumable_items CASCADE');
        DB::statement('TRUNCATE TABLE suppliers CASCADE');
        DB::statement('TRUNCATE TABLE activity_logs CASCADE');
        DB::statement('TRUNCATE TABLE categories CASCADE');
        DB::statement('TRUNCATE TABLE consumable_categories CASCADE');
        DB::statement('TRUNCATE TABLE consumable_units CASCADE');
        DB::statement('TRUNCATE TABLE divisions CASCADE');
        DB::statement('TRUNCATE TABLE held_bies CASCADE');
        DB::statement('TRUNCATE TABLE locations CASCADE');

        // Hapus file gambar
        $publicPath = storage_path('app/public');
        foreach (glob("$publicPath/products/*") as $f) @unlink($f);
        foreach (glob("$publicPath/audit/*") as $f) @unlink($f);

        return response()->json([
            'success' => true,
            'message' => "Backup otomatis: db_$date.sql\nSemua data berhasil di-reset.",
        ]);
    })->name('backup.reset');

    /** --- ACTIVITY LOGS --- **/
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs');
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);

    /** --- SUPPLIER MANAGEMENT --- **/
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{id}/edit', [SupplierController::class, 'edit']);
    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::post('/suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import');
    Route::get('/suppliers/import-template', [SupplierController::class, 'downloadTemplate'])->name('suppliers.import_template');
    Route::get('/api/suppliers', [SupplierController::class, 'getAll']);
    Route::post('/api/suppliers', [SupplierController::class, 'store']);

});
