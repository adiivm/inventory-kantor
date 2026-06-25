<?php

namespace App\Http\Controllers;

use App\Enums\ProductCondition;
use App\Exports\ProductExport;
use App\Exports\ProductTemplateExport;
use App\Helpers\Activity;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Imports\ProductImport;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Division;
use App\Models\HeldBy;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductLog;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    // Menampilkan halaman form
    public function create()
    {
        $categories = Category::all();
        $divisions = Division::all();
        $held_bies = HeldBy::all();    // Tambahkan ini
        $locations = Location::all(); // Tambahkan ini
        $suppliers = Supplier::orderBy('name')->get(); // Supplier

        // SAMAKAN NAMANYA: Gunakan $product, jangan $b
        $product = new Product;

        // Ambil SKU terakhir (format: IVM-0000001, IVM-0000002, dst)
        // Tidak lagi menggunakan tahun - format world-class yang kontinyu global
        $lastProduct = Product::orderBy('id', 'desc')->first();

        if ($lastProduct) {
            // Extract nomor urut dari SKU terakhir (format: IVM-0000023 -> 0000023 -> 23)
            $parts = explode('-', $lastProduct->sku);
            $lastNumber = (int) end($parts); // Convert ke int (hilangkan leading zero)
            $nextNumber = $lastNumber + 1;
        } else {
            // Database kosong, mulai dari 1
            $nextNumber = 1;
        }

        // Format SKU global kontinyu (8 digit): IVM-00000001, IVM-00000002, dst
        $autoSku = 'IVM-'.str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        // Kirim $product ke view
        return view('product_create', compact('categories', 'divisions', 'autoSku', 'product', 'held_bies', 'locations', 'suppliers'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product); // Gunakan Policy

        // 2. Ambil semua kategori & divisi supaya muncul di dropdown edit
        $categories = Category::all();
        $divisions = Division::all();
        $held_bies = HeldBy::all();    // Tambahkan ini
        $locations = Location::all(); // Tambahkan ini
        $suppliers = Supplier::orderBy('name')->get();

        // 3. Kirim semuanya ke view
        return view('product_edit', compact('product', 'categories', 'divisions', 'held_bies', 'locations', 'suppliers'));
    }

    // Proses hapus data
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('forceDelete', $product);

        // Opsional: Hapus file gambar di storage jika ada agar tidak jadi sampah
        if ($product->images) {
            foreach ($product->images as $img) {
                $path = public_path('storage/products/'.$img->path);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        }

        Activity::logDelete('asset', "Aset {$product->name}", $product, $product->toArray());
        $product->delete(); // Jika tidak pakai SoftDeletes, ini akan hapus permanen

        return redirect()->route('product.index')->with('success', 'Data aset telah dihapus permanen!');
    }

    public function exportPdf(Request $request)
    {
        $query = Product::with(['category', 'division', 'supplier']);

        // ✅ Validasi input terlebih dahulu
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'division_id' => 'nullable|integer',
            'condition' => ['nullable', 'string', Rule::enum(ProductCondition::class)],
            'filter' => 'nullable|string',
        ]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($validated) {
                $q->where('name', 'like', '%'.$validated['search'].'%')
                    ->orWhere('sku', 'like', '%'.$validated['search'].'%');
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

        return $pdf->download('laporan-inventory-'.date('d-m-Y').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Ambil semua filter, pastikan null jika tidak diisi agar class Export mudah memprosesnya
        $filters = [
            'search' => $request->get('search'),
            'category_id' => $request->get('category_id'),
            'division_id' => $request->get('division_id'),
            'filter' => $request->get('filter'),
        ];

        $fileName = 'laporan-inventory-'.date('d-m-Y_H-i').'.xlsx';

        // Pastikan di class ProductExport Mas Bro sudah ada __construct($filters)
        return Excel::download(new ProductExport($filters), $fileName);
    }

    public function store(StoreProductRequest $request)
    {

        // Simpan ke Database Produk
        $product = Product::create([
            'sku' => $request->sku,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'division_id' => $request->division_id,
            'stock' => 1,
            'condition' => $request->condition,
            'price' => $request->price,
            'held_by_id' => $request->held_by_id,
            'location_id' => $request->location_id,
            'usage_type' => $request->usage_type ?? 'individual',
            'purchase_date' => $request->purchase_date,
            'is_active' => 'active',
            'warranty_expiry_date' => $request->warranty_expiry_date,
            'supplier_id' => $request->supplier_id,
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

        Activity::logCreate('asset', "Aset {$product->name} ({$product->sku})", $product, $product->toArray());

        // 4. Proses Upload Banyak Gambar
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                try {
                    $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                    $file->storeAs('products', $filename, 'public');

                    $product->images()->create([
                        'image_path' => $filename,
                        'is_primary' => false,
                    ]);
                } catch (\Exception $e) {
                    return back()->with('error', 'Gagal upload gambar: '.$e->getMessage());
                }
            }
        }

        return redirect('/products')->with('success', 'Barang baru berhasil ditambahkan!');
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::with(['category', 'division', 'heldBy', 'location', 'supplier'])->findOrFail($id);
        $this->authorize('update', $product);

        $oldData = $product->getOriginal();

        // Upload & kompresi gambar
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');
                if (! $path) {
                    return back()->with('error', 'Gagal upload gambar');
                }
                // Kompresi jika > 500KB
                $fullPath = storage_path('app/public/products/'.$filename);
                if (file_exists($fullPath) && filesize($fullPath) > 512000) {
                    try {
                        $img = imagecreatefromstring(file_get_contents($fullPath));
                        if ($img) {
                            [$w, $h] = getimagesize($fullPath);
                            $maxDim = 1920;
                            if ($w > $maxDim || $h > $maxDim) {
                                $ratio = min($maxDim / $w, $maxDim / $h);
                                $newW = (int) ($w * $ratio);
                                $newH = (int) ($h * $ratio);
                                $thumb = imagecreatetruecolor($newW, $newH);
                                imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
                                imagejpeg($thumb, $fullPath, 85);
                                imagedestroy($thumb);
                            }
                            imagedestroy($img);
                        }
                    } catch (\Exception $e) {
                        // Abaikan jika gagal kompresi
                    }
                }
                $product->images()->create([
                    'image_path' => $filename,
                    'is_primary' => false,
                ]);
            }
        }

        // Kumpulkan semua ID relasi yang berubah untuk query batch
        $changedRelasi = [];
        $relasiFields = [
            'category_id' => ['old' => $product->category->name ?? '-', 'model' => Category::class],
            'division_id' => ['old' => $product->division->name ?? '-', 'model' => Division::class],
            'location_id' => ['old' => $product->location->name ?? '-', 'model' => Location::class],
            'held_by_id' => ['old' => $product->heldBy->name ?? '-', 'model' => HeldBy::class],
        ];
        foreach ($relasiFields as $field => $info) {
            if (isset($oldData[$field]) && $oldData[$field] != $request->$field) {
                $changedRelasi[$field] = ['id' => $request->$field, 'old' => $info['old'], 'model' => $info['model']];
            }
        }
        // Supplier (nullable)
        $oldSupplierId = (int) ($oldData['supplier_id'] ?? 0);
        $newSupplierId = (int) ($request->supplier_id ?? 0);
        if ($oldSupplierId !== $newSupplierId) {
            $changedRelasi['supplier_id'] = [
                'id' => $newSupplierId,
                'old' => $product->supplier->name ?? '-',
                'model' => Supplier::class,
            ];
        }

        // Query batch: ambil semua nama baru sekali
        $newNames = [];
        foreach ($changedRelasi as $field => $info) {
            if (! empty($info['id'])) {
                $newNames[$field] = $info['model']::find($info['id'])->name ?? '-';
            } else {
                $newNames[$field] = '(Tidak ada)';
            }
        }

        // Bangun log details
        $logDetails = [];

        if (isset($oldData['condition']) && $oldData['condition'] != $request->condition) {
            $labels = ['ready' => 'Ready', 'repair' => 'Servis', 'broken' => 'Rusak', 'disposed' => 'Dibuang'];
            $logDetails[] = "Kondisi: ({$labels[$oldData['condition']]} → {$labels[$request->condition]})";
        }

        foreach (['name' => 'Nama', 'sku' => 'SKU'] as $key => $label) {
            if (isset($oldData[$key]) && $oldData[$key] != $request->$key) {
                $logDetails[] = "$label: ({$oldData[$key]} → {$request->$key})";
            }
        }
        if (isset($oldData['price']) && (int) $oldData['price'] != (int) $request->price) {
            $logDetails[] = "Harga: ({$oldData['price']} → {$request->price})";
        }

        foreach ($changedRelasi as $field => $info) {
            $logDetails[] = match ($field) {
                'category_id' => "Kategori: ({$info['old']} → {$newNames[$field]})",
                'division_id' => "Divisi: ({$info['old']} → {$newNames[$field]})",
                'location_id' => "Lokasi: ({$info['old']} → {$newNames[$field]})",
                'held_by_id' => "Pemegang: ({$info['old']} → {$newNames[$field]})",
                'supplier_id' => "Supplier: ({$info['old']} → {$newNames[$field]})",
                default => ''
            };
        }

        // Tgl Beli & Garansi
        $dateFields = [
            'purchase_date' => 'Tgl Beli',
            'warranty_expiry_date' => 'Garansi',
        ];
        foreach ($dateFields as $field => $label) {
            $oldVal = ! empty($oldData[$field]) ? Carbon::parse($oldData[$field])->format('d-m-Y') : '-';
            $newVal = $request->$field ? Carbon::parse($request->$field)->format('d-m-Y') : '-';
            if ($oldVal !== $newVal) {
                $logDetails[] = "$label: ($oldVal → $newVal)";
            }
        }

        if ($request->hasFile('images')) {
            $logDetails[] = 'Menambahkan '.count($request->file('images')).' foto baru';
        }

        // Update Database (1 query)
        $product->update([
            'sku' => $request->sku, 'name' => $request->name,
            'category_id' => $request->category_id, 'division_id' => $request->division_id,
            'stock' => 1,
            'condition' => $request->condition,
            'price' => $request->price, 'held_by_id' => $request->held_by_id,
            'location_id' => $request->location_id, 'usage_type' => $request->usage_type,
            'purchase_date' => $request->purchase_date,
            'warranty_expiry_date' => $request->warranty_expiry_date,
            'supplier_id' => $request->supplier_id,
        ]);

        // Log (1 query)
        $description = count($logDetails) > 0 ? implode(' | ', $logDetails) : 'Update info tanpa perubahan data';
        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'UPDATE',
            'description' => $description,
            'old_stock' => $oldData['stock'],
            'new_stock' => 1,
            'user_name' => auth()->user()->name ?? 'Admin',
        ]);

        $newValues = $product->fresh()->toArray();
        Activity::logUpdate('asset', "Aset {$product->name}", $product, $oldData, $newValues);

        return redirect('/products')->with('success', 'Data dan riwayat berhasil diperbarui!');
    }

    public function index(Request $request)
    {
        // 1. Data untuk Modal Create/Edit & Filter Dropdown
        $categories = Category::all();
        $divisions = Division::all();
        $held_bies = HeldBy::all();
        $locations = Location::all();

        // 2. Jika request datang dari DataTables (AJAX)
        if ($request->ajax()) {
            // --- MODIFIKASI: Tambahkan 'latestAudit', 'supplier' di dalam with() ---
            $query = Product::with(['category', 'division', 'heldBy', 'location', 'images', 'latestAudit', 'supplier'])
                ->active()
                ->orderBy('sku', 'desc');

            // --- Search Multi-Field (DataTables search + search_sku) ---
            $searchValue = $request->input('search.value');
            if ($request->has('search_sku') && $request->search_sku != '') {
                $query->where('sku', $request->search_sku);
            } elseif (! empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('sku', 'ILIKE', "%{$searchValue}%")
                        ->orWhere('name', 'ILIKE', "%{$searchValue}%")
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhereHas('division', fn ($dq) => $dq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhereHas('heldBy', fn ($hq) => $hq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhereHas('location', fn ($lq) => $lq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhere('condition', 'ILIKE', "%{$searchValue}%");
                });
            }

            // --- Filter Dropdown ---
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('division_id')) {
                $query->where('division_id', $request->division_id);
            }
            if ($request->filled('held_by_id')) {
                $query->where('held_by_id', $request->held_by_id);
            }
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            // --- Filter Kondisi ---
            if ($request->get('condition') == 'ready') {
                $query->where('condition', 'ready');
            } elseif ($request->get('condition') == 'repair') {
                $query->whereIn('condition', ['repair', 'broken']);
            }

            // --- Filter Garansi ---
            $warranty_status = $request->get('warranty_status');
            if ($warranty_status == 'critical') {
                $query->warrantyCritical();
            } elseif ($warranty_status == 'expired') {
                $query->warrantyExpired();
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="product-checkbox" value="'.$row->id.'">';
                })
                ->addColumn('sku_custom', function ($row) {
                    $expiry = $row->warranty_expiry_date ? Carbon::parse($row->warranty_expiry_date) : null;
                    $now = Carbon::now();

                    if (! $expiry || $expiry->isPast()) {
                        $badge = '<span class="badge bg-secondary text-white d-inline-block p-2">'.$row->sku.'</span>';
                    } elseif ($now->diffInDays($expiry) <= 30) {
                        $badge = '<span class="badge bg-warning text-dark d-inline-block p-2">'.$row->sku.'</span>';
                    } else {
                        $badge = '<span class="badge bg-success text-white d-inline-block p-2">'.$row->sku.'</span>';
                    }

                    return $badge;
                })

                // --- MODIFIKASI: Logika Kolom Nama Asset & Audit ---
                ->addColumn('asset_info', function ($row) {
                    $auditData = $row->latestAudit; // Mengambil relasi latestOfMany

                    $lastAudit = $auditData ? Carbon::parse($auditData->audit_date)->format('d/m/Y') : '-';

                    // Logika Warna & Status
                    $statusAudit = 'Belum Pernah';
                    $colorAudit = 'text-danger'; // Merah (Default: Belum Pernah)

                    if ($auditData) {
                        // Hitung selisih hari dari audit terakhir ke hari ini
                        $daysDiff = Carbon::parse($auditData->audit_date)->diffInDays(now());

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
                                '.$row->name.'
                            </a>
                        </div>
                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 11px;">Status: <span class="'.$colorAudit.' fw-bold">'.$statusAudit.'</span></small><br>
                            <small class="text-muted" style="font-size: 11px;">Terakhir Audit: '.$lastAudit.'</small>
                        </div>';
                })

                ->addColumn('image_thumb', function ($row) {
                    $primary = $row->images->where('is_primary', 1)->first();
                    $imageName = $primary ? $primary->image_path : ($row->image ?? ($row->images->first()->image_path ?? null));
                    if ($imageName) {
                        $cleanImageName = str_replace('products/', '', $imageName);
                        $url = asset('storage/products/'.$cleanImageName);
                    } else {
                        $url = asset('images/no-image.png');
                    }

                    return '<img src="'.$url.'" class="rounded shadow-sm border" width="60" height="40" style="object-fit: cover; cursor:pointer" onclick="getDetail('.$row->id.')">';
                })
                ->addColumn('condition_badge', function ($row) {
                    $status = strtolower($row->condition ?? 'ready');
                    $badgeClass = 'bg-secondary';
                    if ($status == 'ready' || $status == 'baik') {
                        $badgeClass = 'bg-success';
                    } elseif ($status == 'broken' || $status == 'rusak') {
                        $badgeClass = 'bg-danger';
                    } elseif ($status == 'repair' || $status == 'servis') {
                        $badgeClass = 'bg-warning text-dark';
                    } elseif ($status == 'disposed' || $status == 'musnah') {
                        $badgeClass = 'bg-dark';
                    }
                    $kondisiTeks = ucfirst($row->condition ?? 'Ready');

                    return '<span class="badge '.$badgeClass.' px-3 py-2 shadow-sm" style="font-size: 0.8rem; letter-spacing: 0.5px;">'.$kondisiTeks.'</span>';
                })
                ->addColumn('holder_info', function ($row) {
                    $holder = $row->heldBy ? $row->heldBy->name : '-';
                    $location = $row->location ? $row->location->name : 'Ruang Belum Diatur';

                    return '<strong>'.$holder.'</strong><br><small class="text-muted">'.$location.'</small>';
                })
                ->addColumn('audit_info', function ($row) {
                    $audit = $row->latestAudit;
                    if (! $audit) {
                        return '<span class="text-danger small">Belum pernah</span>';
                    }
                    $tgl = Carbon::parse($audit->audit_date)->format('d/m/Y');
                    $auditor = $audit->auditor_name ?? '-';
                    $notes = $audit->notes ? '<br><small class="text-muted text-truncate" style="max-width: 150px; display: inline-block;" title="'.$audit->notes.'">'.substr($audit->notes, 0, 30).'...</small>' : '';

                    return '<strong class="text-primary">'.$auditor.'</strong><br><small class="text-muted">'.$tgl.'</small>'.$notes;
                })
                ->addColumn('action', function ($row) {
                    $deleteBtn = Gate::allows('admin-only') ? '
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
                            '.$deleteBtn.'
                        </div>';
                })
                ->rawColumns(['checkbox', 'sku_custom', 'asset_info', 'image_thumb', 'condition_badge', 'holder_info', 'action'])
                ->make(true);
        }

        // 3. Jika load halaman pertama kali (Bukan AJAX)
        $barang = Product::with(['images', 'category'])
            ->orderBy('sku', 'desc')
            ->get();

        return view('products', compact('categories', 'divisions', 'held_bies', 'locations', 'barang'));
    }

    public function getAllLogs($id)
    {
        // Ambil log sistem (ProductLog)
        $systemLogs = ProductLog::where('product_id', $id)->latest()->get();

        // Ambil log audit fisik (AuditLog dari migration baru Mas Bro)
        $auditLogs = AuditLog::where('product_id', $id)->latest()->get();

        return response()->json([
            'system_html' => view('products.partials.log_system', compact('systemLogs'))->render(),
            'audit_html' => view('products.partials.log_audit', compact('auditLogs'))->render(),
        ]);
    }

    public function getProductLogs($id)
    {
        $product = Product::with(['auditLogs.user'])->findOrFail($id);

        // Kita kirimkan tampilan tabel kecil untuk isi modalnya
        $html = '<table class="table table-sm">';
        $html .= '<thead class="table-dark"><tr><th>Tanggal</th><th>User</th><th>Aksi</th><th>Keterangan</th></tr></thead><tbody>';

        if ($product->auditLogs->count() > 0) {
            foreach ($product->auditLogs as $log) {
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
    public function archive(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);

        // Ambil status dari form (jual/destroy/archive)
        // Jika tidak ada, default ke 'archive'
        $statusInput = $request->status ?? 'archive';

        // 1. Update kolom is_active saja (kolom reason tidak ada di database)
        $product->update([
            'is_active' => $statusInput,
        ]);

        // 2. Catat Log dengan alasan (disimpan di log, bukan di column reason)
        ProductLog::create([
            'product_id' => $product->id,
            'action' => strtoupper($statusInput),
            'description' => 'Status diubah ke '.strtoupper($statusInput).'. Alasan: '.($request->reason ?? '-'),
            'user_name' => auth()->user()->name ?? 'Admin',
        ]);
        Activity::log('asset', 'archive', "Aset {$product->name} di-{$statusInput}", $product);

        return redirect()->route('product.index')->with('success', 'Barang berhasil dipindahkan ke Gudang Archive!');
    }

    public function trash(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with(['category', 'division', 'logs', 'heldBy', 'location', 'supplier', 'latestAudit'])
                ->notActive();

            $searchValue = $request->input('search.value');
            if (! empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'ILIKE', "%{$searchValue}%")
                        ->orWhere('sku', 'ILIKE', "%{$searchValue}%")
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhereHas('division', fn ($dq) => $dq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhereHas('heldBy', fn ($hq) => $hq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhereHas('location', fn ($lq) => $lq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'ILIKE', "%{$searchValue}%"))
                        ->orWhere('condition', 'ILIKE', "%{$searchValue}%")
                        ->orWhere('is_active', 'ILIKE', "%{$searchValue}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->filled('division_id')) {
                $query->where('division_id', $request->division_id);
            }
            if ($request->filled('held_by_id')) {
                $query->where('held_by_id', $request->held_by_id);
            }
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }
            if ($request->filled('condition')) {
                $query->where('condition', $request->condition);
            }
            if ($request->filled('is_active_status')) {
                $query->where('is_active', $request->is_active_status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('asset_name', function ($row) {
                    return '
                        <strong class="d-block text-primary fs-6">'.$row->name.'</strong>
                        <span class="badge bg-'.$row->warranty_color.' shadow-sm">'.$row->sku.'</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    $icons = ['jual' => 'cash-coin', 'destroy' => 'hammer', 'archive' => 'box-seam'];
                    $classes = ['jual' => 'bg-success', 'destroy' => 'bg-danger', 'archive' => 'bg-secondary'];
                    $label = strtoupper($row->is_active);
                    $icon = $icons[$row->is_active] ?? 'archive';
                    $class = $classes[$row->is_active] ?? 'bg-dark';

                    return '<span class="badge '.$class.' px-3 py-2 shadow-sm"><i class="bi bi-'.$icon.' me-1"></i> '.$label.'</span>';
                })
                ->addColumn('description', function ($row) {
                    $desc = $row->logs()->latest()->first()->description ?? 'Tidak ada catatan spesifik.';

                    return '<small class="text-muted text-wrap">'.e($desc).'</small>';
                })
                ->addColumn('condition_badge', function ($row) {
                    $cond = strtolower($row->condition ?? 'ready');
                    $classes = ['ready' => 'bg-success', 'repair' => 'bg-warning text-dark', 'broken' => 'bg-danger', 'disposed' => 'bg-secondary'];
                    $labels = ['ready' => 'Ready', 'repair' => 'Servis', 'broken' => 'Rusak', 'disposed' => 'Dibuang'];
                    $class = $classes[$cond] ?? 'bg-secondary';
                    $label = $labels[$cond] ?? ucfirst($cond);

                    return '<span class="badge '.$class.' fs-6">'.$label.'</span>';
                })
                ->addColumn('action', function ($row) {
                    $deleteBtn = Gate::allows('admin-only') ? '
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'" title="Hapus Permanen">
                            <i class="bi bi-trash"></i>
                        </button>' : '';

                    return '<div class="action-buttons-archive">
                        <button type="button" class="btn btn-sm btn-outline-info btn-detail" data-id="'.$row->id.'" title="Detail">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning btn-logs" data-id="'.$row->id.'" title="Log History">
                            <i class="bi bi-clock-history"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-success btn-restore" data-id="'.$row->id.'" title="Restore">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                        '.$deleteBtn.'
                    </div>';
                })
                ->rawColumns(['asset_name', 'status_badge', 'description', 'condition_badge', 'action'])
                ->make(true);
        }

        $categories = Category::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $held_bies = HeldBy::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('product_trash', compact('categories', 'divisions', 'held_bies', 'locations'));
    }

    public function restore($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => 'active']);

        ProductLog::create([
            'product_id' => $product->id,
            'action' => 'RESTORE',
            'description' => 'Barang dikembalikan ke status aktif.',
            'user_name' => auth()->user()->name ?? 'Admin',
        ]);

        Activity::log('asset', 'restore', "Aset {$product->name} dikembalikan ke aktif", $product);

        return redirect()->route('product.index')->with('success', 'Barang berhasil dikembalikan!');
    }

    public function getDetail($id)
    {
        $product = Product::with(['category', 'division', 'heldBy', 'location'])->findOrFail($id);

        if (! $product) {
            return "<div class='alert alert-danger'>Data tidak ditemukan!</div>";
        }

        return view('products.partial_detail', compact('product'));
    }

    public function deleteImage($id)
    {
        $image = ProductImage::findOrFail($id);

        if (Storage::disk('public')->exists('products/'.$image->image_path)) {
            Storage::disk('public')->delete('products/'.$image->image_path);
        }

        $productId = $image->product_id;
        $image->delete();

        ProductLog::create([
            'product_id' => $productId,
            'action' => 'UPDATE',
            'description' => 'Menghapus salah satu foto produk',
            'user_name' => auth()->user()->name ?? 'Admin',
        ]);

        $product = Product::find($productId);
        Activity::log('asset', 'update', "Foto {$product->name} dihapus", $product);

        return response()->json(['success' => 'Foto berhasil dihapus']);
    }

    public function setPrimaryImage($id)
    {
        $image = ProductImage::findOrFail($id);

        // 1. Reset semua gambar produk ini jadi false
        ProductImage::where('product_id', $image->product_id)
            ->update(['is_primary' => false]);

        // 2. Set gambar terpilih jadi true
        $image->update(['is_primary' => true]);

        // 3. UPDATE kolom image di tabel products (KUNCI AGAR DI INDEX BERUBAH)
        $product = Product::find($image->product_id);
        $product->update(['image' => $image->image_path]);

        return response()->json(['success' => 'Foto utama berhasil diganti!']);
    }

    public function getApiData($id)
    {
        // Ambil data 1 produk beserta relasinya
        $product = Product::with(['category', 'images', 'supplier'])->findOrFail($id);

        return response()->json($product);
    }

    public function show($id)
    {
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
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new ProductImport;
            Excel::import($import, $request->file('file'));

            $count = $import->getCount() ?? 0;

            if ($count > 0) {
                Activity::log('asset', 'import', "Import {$count} produk dari Excel");

                if ($request->expectsJson()) {
                    return response()->json(['message' => "Import berhasil! {$count} produk telah ditambahkan."]);
                }

                return redirect()->back()->with('success', "Import berhasil! {$count} produk telah ditambahkan.");
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Import gagal: File tidak memiliki data yang valid.'], 422);
            }

            return redirect()->back()->with('error', 'Import gagal: File tidak memiliki data yang valid.');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: ".implode(', ', $failure->errors());
            }
            $errorMsg = 'Import gagal: '.implode(' | ', $errors);
            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMsg], 422);
            }

            return redirect()->back()->with('error', $errorMsg);
        } catch (\Exception $e) {
            $errorMsg = 'Import gagal: '.$e->getMessage();
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
            'product_ids.*' => 'integer',
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
