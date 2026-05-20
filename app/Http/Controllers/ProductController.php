<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use App\Exports\ProductTemplateExport;
use App\Models\Category;
use App\Models\Division;
use App\Models\ProductLog;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;
use App\Models\HeldBy; 
use App\Models\Location;

use App\Models\Supplier;

use App\Models\ProductImage;

class ProductController extends Controller
{
    // Menampilkan halaman form
    public function create() {
        $categories = Category::all();
        $divisions = Division::all();
        $held_bies = HeldBy::all();    // Tambahkan ini
        $locations = Location::all(); // Tambahkan ini
        $suppliers = Supplier::orderBy('name')->get(); // Supplier

        // SAMAKAN NAMANYA: Gunakan $product, jangan $b
        $product = new \App\Models\Product(); 
        
        // Ambil SKU terakhir (format: IVM-0000001, IVM-0000002, dst)
        // Tidak lagi menggunakan tahun - format world-class yang kontinyu global
        $lastProduct = Product::orderBy('id', 'desc')->first();
        
        if ($lastProduct) {
            // Extract nomor urut dari SKU terakhir (format: IVM-0000023 -> 0000023 -> 23)
            $parts = explode('-', $lastProduct->sku);
            $lastNumber = (int)end($parts); // Convert ke int (hilangkan leading zero)
            $nextNumber = $lastNumber + 1;
        } else {
            // Database kosong, mulai dari 1
            $nextNumber = 1;
        }
        
        // Format SKU global kontinyu (7 digit): IVM-0000001, IVM-0000002, dst
        $autoSku = 'IVM-' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        // Kirim $product ke view
        return view('product_create', compact('categories', 'divisions', 'autoSku', 'product', 'held_bies','locations', 'suppliers'));
    }

    public function edit($id) {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product); // Gunakan Policy

        // 2. Ambil semua kategori & divisi supaya muncul di dropdown edit
        $categories = Category::all();
        $divisions = Division::all();
        $held_bies = HeldBy::all();    // Tambahkan ini
        $locations = Location::all(); // Tambahkan ini
        $suppliers = Supplier::orderBy('name')->get();

        // 3. Kirim semuanya ke view
        return view('product_edit', compact('product', 'categories', 'divisions', 'held_bies','locations', 'suppliers'));
    }

    // Proses hapus data
    public function destroy($id) {
        $product = Product::findOrFail($id);
        $this->authorize('forceDelete', $product);
        
        // Opsional: Hapus file gambar di storage jika ada agar tidak jadi sampah
        if ($product->images) {
            foreach ($product->images as $img) {
                $path = public_path('storage/products/' . $img->path);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        }

        $product->delete(); // Jika tidak pakai SoftDeletes, ini akan hapus permanen

        return redirect()->route('product.index')->with('success', 'Data aset telah dihapus permanen!');
    }

    public function exportPdf(Request $request) {
        $query = Product::with(['category', 'division', 'supplier']);

        // ✅ Validasi input terlebih dahulu
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'division_id' => 'nullable|integer',
            'condition' => 'nullable|string|in:ready,repair,broken',
            'filter' => 'nullable|string',
        ]);

        if ($request->filled('search')) {
            $query->where(function($q) use ($validated) {
                $q->where('name', 'like', '%' . $validated['search'] . '%')
                  ->orWhere('sku', 'like', '%' . $validated['search'] . '%');
            });
        }
        
        // Filter Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter Divisi
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        // Filter Periode (Jika Mas Bro ada filter range tanggal)
        if ($request->filled('filter')) {
            // Logika filter khusus Mas Bro di sini (misal: 'today', 'this_month')
        }

        // Tambahkan ini sebelum $barang = $query->get();
        if ($request->filled('condition')) {
            if ($request->condition == 'ready') {
                $query->where('stock_ready', '>', 0);
            } elseif ($request->condition == 'repair') {
                $query->where('stock_repair', '>', 0);
            } elseif ($request->condition == 'broken') {
                $query->where('stock_broken', '>', 0);
            }
        }

        $barang = $query->get();

        // Setting DomPDF agar bisa baca gambar lokal/storage
        $pdf = Pdf::loadView('product_pdf', compact('barang'))
                ->setPaper('a4', 'landscape'); // Landscape biasanya lebih rapi untuk tabel lebar

        return $pdf->download('laporan-inventory-' . date('d-m-Y') . '.pdf');
    }

    public function exportExcel(Request $request) {
        // Ambil semua filter, pastikan null jika tidak diisi agar class Export mudah memprosesnya
        $filters = [
            'search'      => $request->get('search'),
            'category_id' => $request->get('category_id'),
            'division_id' => $request->get('division_id'),
            'filter'      => $request->get('filter'),
        ];

        $fileName = 'laporan-inventory-' . date('d-m-Y_H-i') . '.xlsx';

        // Pastikan di class ProductExport Mas Bro sudah ada __construct($filters)
        return Excel::download(new ProductExport($filters), $fileName);
    }

    public function store(Request $request) {
        // 1. Validasi
        $request->validate([
            'sku'            => 'required|unique:products,sku',
            'name'           => 'required',
            'condition'      => 'required|in:ready,repair,broken,disposed',
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'warranty_expiry_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
        ], [
            'sku.unique' => 'Waduh Mas Bro, SKU ini sudah dipakai barang lain!'
        ]);

        // Simpan ke Database Produk
        $product = Product::create([
            'sku'           => $request->sku,
            'name'          => $request->name,
            'category_id'   => $request->category_id,
            'division_id'   => $request->division_id,
            'stock'         => 1,
            'condition'     => $request->condition,
            'price'         => $request->price,
            'held_by_id'    => $request->held_by_id,       
            'location_id'   => $request->location_id,     
            'usage_type'    => $request->usage_type ?? 'individual', 
            'purchase_date' => $request->purchase_date,
            'is_active'     => 'active',
            'warranty_expiry_date' => $request->warranty_expiry_date,
            'supplier_id'    => $request->supplier_id,
        ]);

        // Log untuk CREATE
        $supplierName = $request->supplier_id ? Supplier::find($request->supplier_id)->name : '-';
        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'CREATE',
            'description' => "Barang baru ditambahkan (Supplier: $supplierName)",
            'old_stock' => 0,
            'new_stock' => 1,
            'user_name' => auth()->user()->name ?? 'Admin',
        ]);

        // 4. Proses Upload Banyak Gambar
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                try {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('products', $filename, 'public');
                    
                    $product->images()->create([
                        'image_path' => $filename,
                        'is_primary' => false
                    ]);
                } catch (\Exception $e) {
                    return back()->with('error', 'Gagal upload gambar: ' . $e->getMessage());
                }
            }
        }

        return redirect('/products')->with('success', 'Barang baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id) {
        $product = Product::with(['category', 'division', 'heldBy', 'location'])->findOrFail($id);
        $this->authorize('update', $product);
        
        $oldData = $product->getOriginal();
        $oldCategoryName = $product->category->name ?? '-';
        $oldDivisionName = $product->division->name ?? '-';
        $oldHeldByName   = $product->heldBy->name ?? '-';
        $oldLocationName = $product->location->name ?? '-';

        $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'name'  => 'required',
            'condition' => 'required|in:ready,repair,broken,disposed',
            'warranty_expiry_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
        ]);

$filename = $product->image;
        
        // Proses upload gambar dulu
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                try {
                    $file->storeAs('products', $filename, 'public');
                } catch (\Exception $e) {
                    return back()->with('error', 'Gagal upload: ' . $e->getMessage());
                }
                $product->images()->create([
                    'image_path' => $filename,
                    'is_primary' => false 
                ]);
            }
        }

        // CEK PERUBAHAN SEBELUM UPDATE
        $logDetails = [];
        
        // A. Condition
        if (isset($oldData['condition']) && $oldData['condition'] != $request->condition) {
            $conditionLabels = ['ready' => 'Ready', 'repair' => 'Servis', 'broken' => 'Rusak', 'disposed' => 'Dibuang'];
            $oldLabel = $conditionLabels[$oldData['condition']] ?? $oldData['condition'];
            $newLabel = $conditionLabels[$request->condition] ?? $request->condition;
            $logDetails[] = "Kondisi: ($oldLabel → $newLabel)";
        }

        // B. Data Teks
        $textFields = ['name' => 'Nama', 'sku' => 'SKU'];
        foreach ($textFields as $key => $label) {
            if (isset($oldData[$key]) && $oldData[$key] != $request->$key) {
                $logDetails[] = "$label: ({$oldData[$key]} → {$request->$key})";
            }
        }
        if (isset($oldData['price']) && (int)$oldData['price'] != (int)$request->price) {
            $logDetails[] = "Harga: ({$oldData['price']} → {$request->price})";
        }

        // C. Relasi
        if (isset($oldData['category_id']) && $oldData['category_id'] != $request->category_id) {
            $newCat = \App\Models\Category::find($request->category_id)->name ?? '-';
            $logDetails[] = "Kategori: ($oldCategoryName → $newCat)";
        }
        if (isset($oldData['division_id']) && $oldData['division_id'] != $request->division_id) {
            $newDiv = \App\Models\Division::find($request->division_id)->name ?? '-';
            $logDetails[] = "Divisi: ($oldDivisionName → $newDiv)";
        }
        if (isset($oldData['location_id']) && $oldData['location_id'] != $request->location_id) {
            $newLoc = \App\Models\Location::find($request->location_id)->name ?? '-';
            $logDetails[] = "Lokasi: ($oldLocationName → $newLoc)";
        }
        if (isset($oldData['held_by_id']) && $oldData['held_by_id'] != $request->held_by_id) {
            $newHeld = \App\Models\HeldBy::find($request->held_by_id)->name ?? '-';
            $logDetails[] = "Pemegang: ($oldHeldByName → $newHeld)";
        }
        
        // Supplier
        $oldSupplierId = isset($oldData['supplier_id']) ? (int)$oldData['supplier_id'] : null;
        $newSupplierId = $request->supplier_id ? (int)$request->supplier_id : null;
        
        if ($oldSupplierId !== $newSupplierId) {
            $oldSupplier = $oldSupplierId ? (\App\Models\Supplier::find($oldSupplierId)->name ?? '-') : '(Tidak ada)';
            $newSupplier = $newSupplierId ? (\App\Models\Supplier::find($newSupplierId)->name ?? '-') : '(Tidak ada)';
            $logDetails[] = "Supplier: $oldSupplier → $newSupplier";
        }

        // Tgl Beli
        if (isset($oldData['purchase_date'])) {
            $oldDate = $oldData['purchase_date'] ? \Carbon\Carbon::parse($oldData['purchase_date'])->format('Y-m-d') : null;
            $newDate = $request->purchase_date ? \Carbon\Carbon::parse($request->purchase_date)->format('Y-m-d') : null;
            if ($oldDate !== $newDate) {
                $oldP = \Carbon\Carbon::parse($oldData['purchase_date'])->format('d-m-Y');
                $newP = \Carbon\Carbon::parse($request->purchase_date)->format('d-m-Y');
                $logDetails[] = "Tgl Beli: ($oldP → $newP)";
            }
        }

        // Garansi
        if (isset($oldData['warranty_expiry_date'])) {
            $oldDate = $oldData['warranty_expiry_date'] ? \Carbon\Carbon::parse($oldData['warranty_expiry_date'])->format('Y-m-d') : null;
            $newDate = $request->warranty_expiry_date ? \Carbon\Carbon::parse($request->warranty_expiry_date)->format('Y-m-d') : null;
            if ($oldDate !== $newDate) {
                $oldW = \Carbon\Carbon::parse($oldData['warranty_expiry_date'])->format('d-m-Y');
                $newW = \Carbon\Carbon::parse($request->warranty_expiry_date)->format('d-m-Y');
                $logDetails[] = "Garansi: ($oldW → $newW)";
            }
        }

        // Tambah log jika ada foto baru
        if ($request->hasFile('images')) {
            $logDetails[] = "Menambahkan " . count($request->file('images')) . " foto baru";
        }

        // Update Database
        $product->update([
            'sku' => $request->sku, 'name' => $request->name, 'image' => $filename,
            'category_id' => $request->category_id, 'division_id' => $request->division_id,
            'stock' => 1,
            'condition' => $request->condition,
            'price' => $request->price, 'held_by_id' => $request->held_by_id,       
            'location_id' => $request->location_id, 'usage_type' => $request->usage_type,
            'purchase_date' => $request->purchase_date,
            'warranty_expiry_date' => $request->warranty_expiry_date,
            'supplier_id' => $request->supplier_id,
        ]);

        // CEK PERUBAHAN SEBELUM UPDATE
        $logDetails = [];
        
        // A. Condition
        if (isset($oldData['condition']) && $oldData['condition'] != $request->condition) {
            $conditionLabels = ['ready' => 'Ready', 'repair' => 'Servis', 'broken' => 'Rusak', 'disposed' => 'Dibuang'];
            $oldLabel = $conditionLabels[$oldData['condition']] ?? $oldData['condition'];
            $newLabel = $conditionLabels[$request->condition] ?? $request->condition;
            $logDetails[] = "Kondisi: ($oldLabel → $newLabel)";
        }

        // B. Data Teks (Lama -> Baru)
        $textFields = ['name' => 'Nama', 'sku' => 'SKU'];
        foreach ($textFields as $key => $label) {
            if (isset($oldData[$key]) && $oldData[$key] != $request->$key) {
                $logDetails[] = "$label: ({$oldData[$key]} → {$request->$key})";
            }
        }
        // Price comparison (handle number vs string)
        if (isset($oldData['price']) && (int)$oldData['price'] != (int)$request->price) {
            $logDetails[] = "Harga: ({$oldData['price']} → {$request->price})";
        }

        // C. Relasi (Nama Lama -> Nama Baru)
        if (isset($oldData['category_id']) && $oldData['category_id'] != $request->category_id) {
            $newCat = \App\Models\Category::find($request->category_id)->name ?? '-';
            $logDetails[] = "Kategori: ($oldCategoryName → $newCat)";
        }
        if (isset($oldData['division_id']) && $oldData['division_id'] != $request->division_id) {
            $newDiv = \App\Models\Division::find($request->division_id)->name ?? '-';
            $logDetails[] = "Divisi: ($oldDivisionName → $newDiv)";
        }
        if (isset($oldData['location_id']) && $oldData['location_id'] != $request->location_id) {
            $newLoc = \App\Models\Location::find($request->location_id)->name ?? '-';
            $logDetails[] = "Lokasi: ($oldLocationName → $newLoc)";
        }
        if (isset($oldData['held_by_id']) && $oldData['held_by_id'] != $request->held_by_id) {
            $newHeld = \App\Models\HeldBy::find($request->held_by_id)->name ?? '-';
            $logDetails[] = "Pemegang: ($oldHeldByName → $newHeld)";
        }
        
        // Supplier
        $oldSupplierId = isset($oldData['supplier_id']) ? (int)$oldData['supplier_id'] : null;
        $newSupplierId = $request->supplier_id ? (int)$request->supplier_id : null;
        if ($oldSupplierId !== $newSupplierId) {
            $oldSupplier = $oldSupplierId ? (\App\Models\Supplier::find($oldSupplierId)->name ?? '-') : '(Tidak ada)';
            $newSupplier = $newSupplierId ? (\App\Models\Supplier::find($newSupplierId)->name ?? '-') : '(Tidak ada)';
            $logDetails[] = "Supplier: $oldSupplier → $newSupplier";
        }

        // Tgl Beli - parsing karena oldData bisa berupa ISO string
        if (!empty($oldData['purchase_date'])) {
            $oldDateVal = \Carbon\Carbon::parse($oldData['purchase_date'])->format('Y-m-d');
        } else {
            $oldDateVal = null;
        }
        $newDateVal = $request->purchase_date ? \Carbon\Carbon::parse($request->purchase_date)->format('Y-m-d') : null;
        
        if ($oldDateVal !== $newDateVal) {
            $oldP = !empty($oldDateVal) ? \Carbon\Carbon::parse($oldDateVal)->format('d-m-Y') : '-';
            $newP = !empty($newDateVal) ? \Carbon\Carbon::parse($newDateVal)->format('d-m-Y') : '-';
            $logDetails[] = "Tgl Beli: ($oldP → $newP)";
        }

        // Garansi
        if (!empty($oldData['warranty_expiry_date'])) {
            $oldWarrantyVal = \Carbon\Carbon::parse($oldData['warranty_expiry_date'])->format('Y-m-d');
        } else {
            $oldWarrantyVal = null;
        }
        $newWarrantyVal = $request->warranty_expiry_date ? \Carbon\Carbon::parse($request->warranty_expiry_date)->format('Y-m-d') : null;
        
        if ($oldWarrantyVal !== $newWarrantyVal) {
            $oldW = !empty($oldWarrantyVal) ? \Carbon\Carbon::parse($oldWarrantyVal)->format('d-m-Y') : '-';
            $newW = !empty($newWarrantyVal) ? \Carbon\Carbon::parse($newWarrantyVal)->format('d-m-Y') : '-';
            $logDetails[] = "Garansi: ($oldW → $newW)";
        }

        // Tambah log jika ada foto baru
        if ($request->hasFile('images')) {
            $logDetails[] = "Menambahkan " . count($request->file('images')) . " foto baru";
        }

        // Update Database
        $product->update([
            'sku' => $request->sku, 'name' => $request->name, 'image' => $filename,
            'category_id' => $request->category_id, 'division_id' => $request->division_id,
            'stock' => 1,
            'condition' => $request->condition,
            'price' => $request->price, 'held_by_id' => $request->held_by_id,       
            'location_id' => $request->location_id, 'usage_type' => $request->usage_type,
            'purchase_date' => $request->purchase_date,
            'warranty_expiry_date' => $request->warranty_expiry_date,
        ]);

        $description = count($logDetails) > 0 ? implode(' | ', $logDetails) : "Update info tanpa perubahan data";

        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'UPDATE',
            'description' => $description,
            'old_stock' => $oldData['stock'],
            'new_stock' => 1, // Stock selalu 1 karena 1 SKU = 1 item
            'user_name' => auth()->user()->name ?? 'Admin', 
        ]);

        return redirect('/products')->with('success', 'Data dan riwayat berhasil diperbarui!');
    }

    public function index(Request $request) {
        // 1. Data untuk Modal Create/Edit & Filter Dropdown
        $categories = \App\Models\Category::all(); 
        $divisions = \App\Models\Division::all();
        $held_bies = \App\Models\HeldBy::all(); 
        $locations = \App\Models\Location::all();

        // 2. Jika request datang dari DataTables (AJAX)
        if ($request->ajax()) {
            // --- MODIFIKASI: Tambahkan 'latestAudit' di dalam with() ---
            $query = \App\Models\Product::with(['category', 'division', 'heldBy', 'location', 'images', 'latestAudit'])
                    ->where('is_active', 'active')
                    ->orderBy('sku', 'desc');

            // --- Filter Dashboard (Tetap Sama) ---
            if ($request->filter == 'stok_menipis') {
                $query->where('stock', '<=', 5)->where('stock', '>', 0);
            } elseif ($request->filter == 'repairing') {
                $query->where('stock_repair', '>', 0);
            } elseif ($request->filter == 'stok_habis') {
                $query->where('stock', '<=', 0);
            }

            // --- Filter Dropdown (Tetap Sama) ---
            if ($request->filled('category_id')) { $query->where('category_id', $request->category_id); }
            if ($request->filled('division_id')) { $query->where('division_id', $request->division_id); }
            if ($request->filled('held_by_id')) { $query->where('held_by_id', $request->held_by_id); }
            if ($request->filled('location_id')) { $query->where('location_id', $request->location_id); }

            // --- Filter Kondisi ---
            if ($request->get('condition') == 'ready') {
                $query->where('condition', 'ready');
            } elseif ($request->get('condition') == 'repair') {
                $query->whereIn('condition', ['repair', 'broken']);
            }

            // --- Filter Garansi ---
            $today = now()->format('Y-m-d');
            $thirtyDaysFromNow = now()->addDays(30)->format('Y-m-d');
            
            $warranty_status = $request->get('warranty_status');
            if ($warranty_status == 'critical') {
                $query->whereNotNull('warranty_expiry_date')
                      ->whereDate('warranty_expiry_date', '>=', $today)
                      ->whereDate('warranty_expiry_date', '<=', $thirtyDaysFromNow);
            } elseif ($warranty_status == 'expired') {
                $query->whereNotNull('warranty_expiry_date')
                      ->whereDate('warranty_expiry_date', '<', $today);
            }

            // --- Filter Search SKU (dari klik lonceng) ---
            if ($request->has('search_sku') && $request->search_sku != '') {
                $query->where('sku', $request->search_sku);
            }

            return Datatables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function($row) {
                    return '<input type="checkbox" class="product-checkbox" value="'.$row->id.'">';
                })
                ->addColumn('sku_custom', function($row) {
                    $expiry = $row->warranty_expiry_date ? \Carbon\Carbon::parse($row->warranty_expiry_date) : null;
                    $now = \Carbon\Carbon::now();
                    
                    if (!$expiry || $expiry->isPast()) {
                        $badge = '<span class="badge bg-secondary text-white d-inline-block p-2">' . $row->sku . '</span>';
                    } elseif ($now->diffInDays($expiry) <= 30) {
                        $badge = '<span class="badge bg-warning text-dark d-inline-block p-2">' . $row->sku . '</span>';
                    } else {
                        $badge = '<span class="badge bg-success text-white d-inline-block p-2">' . $row->sku . '</span>';
                    }
                    
                    return $badge;
                })
                
                // --- MODIFIKASI: Logika Kolom Nama Asset & Audit ---
                ->addColumn('asset_info', function($row) {
                    $auditData = $row->latestAudit; // Mengambil relasi latestOfMany
                    
                    $lastAudit = $auditData ? \Carbon\Carbon::parse($auditData->audit_date)->format('d/m/Y') : '-';
                    
                    // Logika Warna & Status
                    $statusAudit = 'Belum Pernah';
                    $colorAudit = 'text-danger'; // Merah (Default: Belum Pernah)

                    if ($auditData) {
                        // Hitung selisih hari dari audit terakhir ke hari ini
                        $daysDiff = \Carbon\Carbon::parse($auditData->audit_date)->diffInDays(now());

                        if ($daysDiff > 30) {
                            $statusAudit = 'Perlu Re-Audit';
                            $colorAudit = 'text-warning'; // Kuning (Peringatan: > 30 Hari)
                        } else {
                            $statusAudit = 'Sudah Diaudit';
                            $colorAudit = 'text-success'; // Hijau (Aman: < 30 Hari)
                        }
                    }

                    return '
                        <div>
                            <a href="javascript:void(0)" onclick="getDetail('.$row->id.')" class="fw-bold text-primary text-decoration-none" style="font-size: 1.05rem;">
                                ' . $row->name . '
                            </a>
                        </div>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 11px;">Status: <span class="'.$colorAudit.' fw-bold">'.$statusAudit.'</span></small><br>
                            <small class="text-muted" style="font-size: 11px;">Terakhir Audit: ' . $lastAudit . '</small>
                        </div>';
                })

                ->addColumn('image_thumb', function($row) {
                    $primary = $row->images->where('is_primary', 1)->first();
                    $imageName = $primary ? $primary->image_path : ($row->image ?? ($row->images->first()->image_path ?? null));
                    if ($imageName) {
                        $cleanImageName = str_replace('products/', '', $imageName);
                        $url = asset('storage/products/' . $cleanImageName);
                    } else { $url = asset('images/no-image.png'); }
                    return '<img src="'.$url.'" class="rounded shadow-sm border" width="60" height="40" style="object-fit: cover; cursor:pointer" onclick="getDetail('.$row->id.')">';
                })
                ->addColumn('condition_badge', function($row) {
                    $status = strtolower($row->condition ?? 'ready'); 
                    $badgeClass = 'bg-secondary';
                    if ($status == 'ready' || $status == 'baik') { $badgeClass = 'bg-success'; } 
                    elseif ($status == 'broken' || $status == 'rusak') { $badgeClass = 'bg-danger'; } 
                    elseif ($status == 'repair' || $status == 'servis') { $badgeClass = 'bg-warning text-dark'; } 
                    elseif ($status == 'disposed' || $status == 'musnah') { $badgeClass = 'bg-dark'; }
                    $kondisiTeks = ucfirst($row->condition ?? 'Ready');
                    return '<span class="badge ' . $badgeClass . ' px-3 py-2 shadow-sm" style="font-size: 0.8rem; letter-spacing: 0.5px;">' . $kondisiTeks . '</span>';
                })
                ->addColumn('holder_info', function($row) {
                    $holder = $row->heldBy ? $row->heldBy->name : '-';
                    $location = $row->location ? $row->location->name : 'Ruang Belum Diatur';
                    return '<strong>' . $holder . '</strong><br><small class="text-muted">' . $location . '</small>';
                })
                ->addColumn('audit_info', function($row) {
                    $audit = $row->latestAudit;
                    if (!$audit) {
                        return '<span class="text-danger small">Belum pernah</span>';
                    }
                    $tgl = \Carbon\Carbon::parse($audit->audit_date)->format('d/m/Y');
                    $auditor = $audit->auditor_name ?? '-';
                    $notes = $audit->notes ? '<br><small class="text-muted text-truncate" style="max-width: 150px; display: inline-block;" title="'.$audit->notes.'">' . substr($audit->notes, 0, 30) . '...</small>' : '';
                    return '<strong class="text-primary">' . $auditor . '</strong><br><small class="text-muted">' . $tgl . '</small>' . $notes;
                })
                ->addColumn('action', function($row) {
                    $deleteBtn = strtolower(auth()->user()->role) === 'admin' ? '
                                <button type="button" class="btn btn-sm btn-danger" onclick="forceDelete('.$row->id.')" title="Delete Permanen">
                                    <i class="bi bi-trash"></i>
                                </button>' : '';
                    
                    return '
                        <div class="d-flex gap-1 justify-content-center justify-content-md-start action-buttons">
                            <button class="btn btn-sm btn-info text-white" onclick="showLogs('.$row->id.')" title="Log History">
                                <i class="bi bi-journal-text"></i>
                            </button>
                            <a href="'.route('product.edit', $row->id).'" class="btn btn-sm btn-warning text-white" title="Edit Data">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="archiveProduct('.$row->id.')" title="Archive to Gudang">
                                <i class="bi bi-box-seam"></i>
                            </button>
                            ' . $deleteBtn . '
                        </div>';
                })
                ->rawColumns(['checkbox', 'sku_custom', 'asset_info', 'image_thumb', 'condition_badge', 'holder_info', 'action'])
                ->make(true);
        }

        // 3. Jika load halaman pertama kali (Bukan AJAX)
        $barang = \App\Models\Product::with(['images', 'category'])
            ->orderBy('sku', 'desc')
            ->get();
        return view('products', compact('categories', 'divisions', 'held_bies', 'locations', 'barang'));
    }

    public function getAllLogs($id)
    {
        // Ambil log sistem (ProductLog)
        $systemLogs = \App\Models\ProductLog::where('product_id', $id)->latest()->get();
        
        // Ambil log audit fisik (AuditLog dari migration baru Mas Bro)
        $auditLogs = \App\Models\AuditLog::where('product_id', $id)->latest()->get();

        return response()->json([
            'system_html' => view('products.partials.log_system', compact('systemLogs'))->render(),
            'audit_html'  => view('products.partials.log_audit', compact('auditLogs'))->render(),
        ]);
    }

    public function getProductLogs($id){
        $product = Product::with(['auditLogs.user'])->findOrFail($id);
        
        // Kita kirimkan tampilan tabel kecil untuk isi modalnya
        $html = '<table class="table table-sm">';
        $html .= '<thead class="table-dark"><tr><th>Tanggal</th><th>User</th><th>Aksi</th><th>Keterangan</th></tr></thead><tbody>';
        
        if($product->auditLogs->count() > 0) {
            foreach($product->auditLogs as $log) {
                $html .= "<tr>
                    <td>{$log->created_at->format('d/m/Y H:i')}</td>
                    <td>".($log->user->name ?? 'System')."</td>
                    <td><span class='badge bg-info'>{$log->action}</span></td>
                    <td>{$log->note}</td>
                </tr>";
            }
        } else {
            $html .= '<tr><td colspan="4" class="text-center text-muted">Belum ada history audit.</td></tr>';
        }
        
        $html .= '</tbody></table>';
        
        return response($html);
    }

    // Di ProductController.php
    public function archive(Request $request, $id) {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);
        
        // Ambil status dari form (jual/destroy/archive)
        // Jika tidak ada, default ke 'archive'
        $statusInput = $request->status ?? 'archive';

        // 1. Update kolom is_active saja (kolom reason tidak ada di database)
        $product->update([
            'is_active' => $statusInput
        ]);

        // 2. Catat Log dengan alasan (disimpan di log, bukan di column reason)
        \App\Models\ProductLog::create([
            'product_id'  => $product->id,
            'action'      => strtoupper($statusInput),
            'description' => "Status diubah ke " . strtoupper($statusInput) . ". Alasan: " . ($request->reason ?? '-'),
            'user_name'   => auth()->user()->name ?? 'Admin'
        ]);

        return redirect()->route('product.index')->with('success', 'Barang berhasil dipindahkan ke Gudang Archive!');
    }

    public function trash() {
        $barang = \App\Models\Product::with(['category', 'division', 'logs'])
                    ->where('is_active', '!=', 'active') 
                    ->latest()
                    ->get();

        return view('product_trash', compact('barang'));
    }

    public function restore($id) {
        $product = \App\Models\Product::findOrFail($id);
        $product->update(['is_active' => 'active']);

        \App\Models\ProductLog::create([
            'product_id'  => $product->id,
            'action'      => 'RESTORE',
            'description' => "Barang dikembalikan ke status aktif.",
            'user_name'   => auth()->user()->name ?? 'Admin'
        ]);

        return redirect()->route('product.index')->with('success', 'Barang berhasil dikembalikan!');
    }

    public function getDetail($id) {
        $product = Product::with(['category', 'division', 'heldBy', 'location'])->findOrFail($id);

        if (!$product) {
            return "<div class='alert alert-danger'>Data tidak ditemukan!</div>";
        }
        return view('products.partial_detail', compact('product'));
    }

    public function deleteImage($id){
        $image = \App\Models\ProductImage::findOrFail($id);
        
        if (Storage::disk('public')->exists('products/' . $image->image_path)) {
            Storage::disk('public')->delete('products/' . $image->image_path);
        }

        $productId = $image->product_id;
        $image->delete();

        \App\Models\ProductLog::create([
            'product_id' => $productId,
            'action' => 'UPDATE',
            'description' => "Menghapus salah satu foto produk",
            'user_name' => auth()->user()->name ?? 'Admin'
        ]);

        return response()->json(['success' => 'Foto berhasil dihapus']);
    }

    public function setPrimaryImage($id){
        $image = \App\Models\ProductImage::findOrFail($id);
        
        // 1. Reset semua gambar produk ini jadi false
        \App\Models\ProductImage::where('product_id', $image->product_id)
            ->update(['is_primary' => false]);

        // 2. Set gambar terpilih jadi true
        $image->update(['is_primary' => true]);

        // 3. UPDATE kolom image di tabel products (KUNCI AGAR DI INDEX BERUBAH)
        $product = \App\Models\Product::find($image->product_id);
        $product->update(['image' => $image->image_path]);

        return response()->json(['success' => 'Foto utama berhasil diganti!']);
    }

    public function getApiData($id){
        // Ambil data 1 produk beserta relasinya
        $product = \App\Models\Product::with(['category', 'images', 'supplier'])->findOrFail($id);
        return response()->json($product);
    }
    
    public function show($id){
        try {
            // Ambil produk beserta relasinya agar carousel & detail muncul
            $product = Product::with(['category', 'division', 'heldBy', 'location', 'images', 'supplier'])->findOrFail($id);
            $product->append('warranty_color');

            // Kirim data dalam bentuk JSON
            return response()->json($product);
            
        } catch (\Exception $e) {
            // Jika ada error, kirim pesan errornya supaya tidak Error 500
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'product_import_template.xlsx');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new ProductImport;
            Excel::import($import, $request->file('file'));
            
            $count = $import->getCount() ?? 0;
            
            if ($count > 0) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => "Import berhasil! {$count} produk telah ditambahkan."]);
                }
                return redirect()->back()->with('success', "Import berhasil! {$count} produk telah ditambahkan.");
            }
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Import gagal: File tidak memiliki data yang valid.'], 422);
            }
            return redirect()->back()->with('error', 'Import gagal: File tidak memiliki data yang valid.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            $errorMsg = 'Import gagal: ' . implode(' | ', $errors);
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        } catch (\Exception $e) {
            $errorMsg = 'Import gagal: ' . $e->getMessage();
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMsg], 500);
            }
            return redirect()->back()->with('error', $errorMsg);
        }
    }

    public function bulkPrintLabels(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer'
        ]);

        $products = Product::with(['category', 'division', 'supplier'])
            ->whereIn('id', $request->product_ids)
            ->get();

        if ($products->isEmpty()) {
            return redirect()->back()->with('error', 'Pilih minimal satu produk dulu untuk dicetak!');
        }

        return view('products.bulk_print_labels', compact('products'));
    }
}
