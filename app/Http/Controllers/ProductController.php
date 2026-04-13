<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Category;
use App\Models\Division;
use App\Models\ProductLog;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;
use App\Models\Held_by; 
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use App\Models\ProductCondition;

class ProductController extends Controller
{
    // Menampilkan halaman form
    public function create() {
        $categories = Category::all();
        $divisions = Division::all();
        $held_bies = Held_by::all();    // Tambahkan ini
        $locations = Location::all(); // Tambahkan ini

        // SAMAKAN NAMANYA: Gunakan $product, jangan $b
        $product = new \App\Models\Product(); 
        
        $lastProduct = Product::latest('id')->first();
        $nextNumber = $lastProduct ? $lastProduct->id + 1 : 1;
        
        $autoSku = 'IVM-' . date('Y') . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Kirim $product ke view
        return view('product_create', compact('categories', 'divisions', 'autoSku', 'product', 'held_bies','locations'));
    }

    public function edit($id) {
        $this->authorize('update', Product::class); // Gunakan Policy
        $product = Product::findOrFail($id);

        // 2. Ambil semua kategori & divisi supaya muncul di dropdown edit
        $categories = Category::all();
        $divisions = Division::all();
        $held_bies = Held_by::all();    // Tambahkan ini
        $locations = Location::all(); // Tambahkan ini

        // 3. Kirim semuanya ke view
        return view('product_edit', compact('product', 'categories', 'divisions', 'held_bies','locations'));
    }

    // Proses hapus data
    public function destroy($id) {
        if (auth()->user()->role !== 'admin') {
            return abort(403, 'Cuma Admin yang boleh hapus permanen, Mas Bro!');
        }
        $product = Product::findOrFail($id);
        
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
        $query = Product::with(['category', 'division']);

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

        // Pastikan di class ProductsExport Mas Bro sudah ada __construct($filters)
        return Excel::download(new ProductsExport($filters), $fileName);
    }

    public function store(Request $request) {
        // 1. Validasi
        $request->validate([
            'sku'            => 'required|unique:products,sku',
            'name'           => 'required',
            'stock_ready'    => 'required|integer|min:0',
            'stock_repair'   => 'required|integer|min:0',
            'stock_broken'   => 'required|integer|min:0',
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png|max:10240', // Validasi tiap file di array
            'warranty_expiry_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
        ], [
            'sku.unique' => 'Waduh Mas Bro, SKU ini sudah dipakai barang lain!'
        ]);

        // 2. Hitung total stok
        $ready  = $request->stock_ready ?? 0;
        $repair = $request->stock_repair ?? 0;
        $broken = $request->stock_broken ?? 0;
        $totalStock = $ready + $repair + $broken;

        // 3. Simpan ke Database Produk TERLEBIH DAHULU
        // Ini agar kita dapat ID Produk untuk relasi gambar
        $product = Product::create([
            'sku'           => $request->sku,
            'name'          => $request->name,
            'category_id'   => $request->category_id,
            'division_id'   => $request->division_id,
            'stock'         => $totalStock, // Pastikan kolom 'stock' ada di tabel
            'stock_ready'   => $ready,
            'stock_repair'  => $repair,
            'stock_broken'  => $broken,
            'price'         => $request->price,
            'held_by_id'    => $request->held_by_id,       
            'location_id'   => $request->location_id,     
            'usage_type'    => $request->usage_type ?? 'individual', 
            'purchase_date' => $request->purchase_date,
            'is_active'     => 'active',
            'warranty_expiry_date' => $request->warranty_expiry_date,
        ]);

        // 4. Proses Upload Banyak Gambar (Cukup SATU kali looping)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // ✅ Compress & resize
                $image = Image::make($file)
                    ->resize(1200, 800, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 75); // 75% quality
                
                $filename = time() . '_' . uniqid() . '.jpg';
                Storage::disk('public')->put('products/' . $filename, $image);
                
                $product->images()->create([
                    'image_path' => $filename,
                    'is_primary' => false // Gambar tambahan
                ]);
            }
        }

        return redirect('/products')->with('success', 'Barang baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id) {
        $this->authorize('update', Product::class);
        $product = Product::with(['category', 'division', 'heldBy', 'location'])->findOrFail($id);
        
        $oldData = $product->toArray();
        $oldCategoryName = $product->category->name ?? '-';
        $oldDivisionName = $product->division->name ?? '-';
        $oldHeldByName   = $product->heldBy->name ?? '-';
        $oldLocationName = $product->location->name ?? '-';

        $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'name'  => 'required',
            'warranty_expiry_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
        ]);
        $totalNew = ($request->stock_ready ?? 0) + ($request->stock_repair ?? 0) + ($request->stock_broken ?? 0);

        $filename = $product->image;
        
        // ✅ HAPUS BAGIAN INI (duplikasi pertama)
        
        // ✅ SATU-SATUNYA upload logic (baris 200-an)
        $logDetails = [];
        
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
            $logDetails[] = "Menambahkan " . count($request->file('images')) . " foto baru";
        }

        // 4. Update Database
        $product->update([
            'sku' => $request->sku, 'name' => $request->name, 'image' => $filename,
            'category_id' => $request->category_id, 'division_id' => $request->division_id,
            'stock' => $totalNew, 'stock_ready' => $request->stock_ready,
            'stock_repair' => $request->stock_repair, 'stock_broken' => $request->stock_broken,
            'price' => $request->price, 'held_by_id' => $request->held_by_id,       
            'location_id' => $request->location_id, 'usage_type' => $request->usage_type,
            'purchase_date' => $request->purchase_date,
            'warranty_expiry_date' => $request->warranty_expiry_date,
        ]);

        // 5. LOGIKA LOG DETAIL (BOOM!)
        $logDetails = [];

        // A. Stok (Selisih)
        $stockFields = ['stock_ready' => 'Ready', 'stock_repair' => 'Repair', 'stock_broken' => 'Broken'];
        foreach ($stockFields as $key => $label) {
            if ($oldData[$key] != $request->$key) {
                $diff = $request->$key - $oldData[$key];
                $tanda = $diff > 0 ? "+" : "";
                $logDetails[] = "$label ($tanda$diff)";
            }
        }

        // B. Data Teks (Lama -> Baru)
        $textFields = ['name' => 'Nama', 'price' => 'Harga', 'sku' => 'SKU'];
        foreach ($textFields as $key => $label) {
            if ($product->wasChanged($key)) {
                $logDetails[] = "$label: ({$oldData[$key]} → {$product->$key})";
            }
        }

        // C. Relasi (Nama Lama -> Nama Baru)
        if ($product->wasChanged('category_id')) {
            $newCat = \App\Models\Category::find($request->category_id)->name ?? '-';
            $logDetails[] = "Kategori: ($oldCategoryName → $newCat)";
        }
        if ($product->wasChanged('division_id')) {
            $newDiv = \App\Models\Division::find($request->division_id)->name ?? '-';
            $logDetails[] = "Divisi: ($oldDivisionName → $newDiv)";
        }
        if ($product->wasChanged('location_id')) {
            $newLoc = \App\Models\Location::find($request->location_id)->name ?? '-';
            $logDetails[] = "Lokasi: ($oldLocationName → $newLoc)";
        }
        if ($product->wasChanged('held_by_id')) {
            $newHeld = \App\Models\Held_by::find($request->held_by_id)->name ?? '-';
            $logDetails[] = "Pemegang: ($oldHeldByName → $newHeld)";
        }
        if ($product->wasChanged('purchase_date')) {
            $oldP = $oldData['purchase_date'] ? \Carbon\Carbon::parse($oldData['purchase_date'])->format('d-m-Y') : '-';
            $newP = $product->purchase_date ? \Carbon\Carbon::parse($product->purchase_date)->format('d-m-Y') : '-';
            $logDetails[] = "Tgl Beli: ($oldP → $newP)";
        }
        if ($product->wasChanged('warranty_expiry_date')) {
            $oldW = $oldData['warranty_expiry_date'] ? \Carbon\Carbon::parse($oldData['warranty_expiry_date'])->format('d-m-Y') : '-';
            $newW = $product->warranty_expiry_date ? \Carbon\Carbon::parse($product->warranty_expiry_date)->format('d-m-Y') : '-';
            $logDetails[] = "Garansi: ($oldW → $newW)";
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Buat nama unik untuk tiap foto
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('products', $filename, 'public');

                // Simpan ke tabel relasi product_images
                $product->images()->create([
                    'image_path' => $filename,
                    'is_primary' => false // Foto tambahan
                ]);
            }
            $logDetails[] = "Menambahkan " . count($request->file('images')) . " foto baru";
        }
        

        $description = count($logDetails) > 0 ? implode(' | ', $logDetails) : "Update info tanpa perubahan data";

        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'UPDATE',
            'description' => $description,
            'old_stock' => $oldData['stock'],
            'new_stock' => $totalNew,
            'user_name' => auth()->user()->name ?? 'Admin', 
        ]);

        return redirect('/products')->with('success', 'Data dan riwayat berhasil diperbarui!');
    }

    public function index(Request $request) {
        // 1. Data untuk Modal Create/Edit & Filter Dropdown
        $categories = \App\Models\Category::all(); 
        $divisions = \App\Models\Division::all();
        $held_bies = \App\Models\Held_by::all(); 
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

            // --- Filter Kondisi (Tetap Sama) ---
            if ($request->filled('condition')) {
                if ($request->condition == 'ready') { $query->where('stock_ready', '>', 0); }
                elseif ($request->condition == 'repair') { $query->where('stock_repair', '>', 0); }
                elseif ($request->condition == 'broken') { $query->where('stock_broken', '>', 0); }
            }

            return Datatables::of($query)
                ->addIndexColumn()
                ->addColumn('sku_custom', function($row) {
                    return '<span class="badge bg-'. $row->warranty_color .' shadow-sm">'. $row->sku .'</span>';
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
                ->addColumn('action', function($row) {
                    $deleteBtn = auth()->user()->role === 'Admin' ? '
                                <button type="button" class="btn btn-sm text-white p-1" style="background-color: #dd4b39; width: 35px;" onclick="forceDelete('.$row->id.')" title="Delete Permanen">
                                    <i class="bi bi-trash fs-6"></i><br><small style="font-size: 9px;">Del</small>
                                </button>' : '';
                    
                    return '
                        <div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-sm text-white p-1" style="background-color: #00c0ef; width: 35px;" onclick="showLogs('.$row->id.')" title="Log History">
                                <i class="bi bi-journal-text fs-6"></i><br><small style="font-size: 9px;">Log</small>
                            </button>
                            <a href="'.route('product.edit', $row->id).'" class="btn btn-sm text-white p-1" style="background-color: #f39c12; width: 35px;" title="Edit Data">
                                <i class="bi bi-pencil fs-6"></i><br><small style="font-size: 9px;">Edit</small>
                            </a>
                            <button type="button" class="btn btn-sm border text-dark bg-white p-1" style="width: 35px;" onclick="archiveProduct('.$row->id.')" title="Archive to Gudang">
                                <i class="bi bi-box-seam fs-6"></i><br><small style="font-size: 9px;">Arc</small>
                            </button>
                            ' . $deleteBtn . '
                        </div>';
                })
                ->rawColumns(['sku_custom', 'asset_info', 'image_thumb', 'condition_badge', 'holder_info', 'action'])
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
        $this->authorize('delete', Product::class);
        $product = Product::findOrFail($id);
        
        // Ambil status dari form (jual/destroy/archive)
        // Jika tidak ada, default ke 'archive'
        $statusInput = $request->status ?? 'archive';

        // 1. Update kolom is_active (sesuai Blade Trash Mas Bro)
        $product->update([
            'is_active' => $statusInput,
            'reason'    => $request->reason
        ]);

        // 2. Catat Log (Gunakan \Auth atau Auth jika sudah di-import)
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
        
        if (\Storage::disk('public')->exists('products/' . $image->image_path)) {
            \Storage::disk('public')->delete('products/' . $image->image_path);
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
        $product = \App\Models\Product::with(['category', 'images'])->findOrFail($id);
        return response()->json($product);
    }
    
    public function show($id){
        try {
            // Ambil produk beserta relasinya agar carousel & detail muncul
            $product = Product::with(['category', 'division', 'heldBy', 'location', 'images'])->findOrFail($id);
            $product->append('warranty_color');

            // Kirim data dalam bentuk JSON
            return response()->json($product);
            
        } catch (\Exception $e) {
            // Jika ada error, kirim pesan errornya supaya tidak Error 500
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
