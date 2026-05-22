@php
    $primaryImage = $product->images->where('is_primary', 1)->first() ?? $product->images->first();
    $imageUrl = $primaryImage ? asset('storage/products/' . $primaryImage->image_path) : asset('images/no-image.png');
    
    $statusClass = $product->is_active == 'active' ? 'success' : ($product->is_active == 'archive' ? 'secondary' : 'danger');
    $statusText = $product->is_active == 'active' ? 'AKTIF' : ($product->is_active == 'archive' ? 'DIARSIPKAN' : strtoupper($product->is_active));
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Audit Aset</title>
    <style>
        body { background: #f4f7fe; }
        .info-card { background: #fff; border-radius: 15px; padding: 15px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .info-label { font-size: 0.75rem; color: #a3adc2; text-transform: uppercase; font-weight: 600; }
        .info-value { font-size: 0.9rem; font-weight: 600; color: #2b3674; }
        .stock-badge { padding: 3px 8px; border-radius: 5px; font-size: 0.75rem; }
        .stock-ready { background: #d4edda; color: #155724; }
        .stock-repair { background: #fff3cd; color: #856404; }
        .stock-broken { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-3">
        <div class="card shadow border-0" style="border-radius: 20px;">
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="{{ $imageUrl }}" class="img-fluid rounded mb-2" style="max-height: 180px; border: 3px solid #4361ee;">
                    <h4 class="fw-bold text-primary">{{ $product->name }}</h4>
                    <span class="badge bg-{{ $product->warranty_color }} px-3 py-2">{{ $product->sku }}</span>
                    <span class="badge bg-{{ $statusClass }} ms-1">{{ $statusText }}</span>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label"><i class="bi bi-tag me-1"></i> Kategori</div>
                            <div class="info-value">{{ $product->category->name ?? 'Belum ditentukan' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label"><i class="bi bi-building me-1"></i> Divisi</div>
                            <div class="info-value">{{ $product->division->name ?? 'Belum ditentukan' }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label"><i class="bi bi-person me-1"></i> Pemegang</div>
                            <div class="info-value">{{ $product->heldBy->name ?? 'Belum ditentukan' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-card">
                            <div class="info-label"><i class="bi bi-geo-alt me-1"></i> Lokasi</div>
                            <div class="info-value">{{ $product->location->name ?? 'Belum ditentukan' }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="info-label mb-2"><i class="bi bi-box-seam me-1"></i> Kondisi</div>
                    <div class="d-flex justify-content-center">
                        @php
                            $cond = strtolower($product->condition ?? 'ready');
                            $badgeClass = $cond == 'ready' ? 'bg-success' : ($cond == 'repair' ? 'bg-warning text-dark' : ($cond == 'broken' ? 'bg-danger' : 'bg-secondary'));
                            $condLabel = $cond == 'ready' ? 'Ready' : ($cond == 'repair' ? 'Servis' : ($cond == 'broken' ? 'Rusak' : 'Dibuang'));
                        @endphp
                        <span class="badge fs-5 {{ $badgeClass }}">{{ $condLabel }}</span>
                    </div>
                </div>
                
                <hr>
                
                <form action="{{ route('audit.submit') }}" method="POST" id="auditForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Auditor</label>
                        <select name="auditor_name" class="form-select" required>
                            <option value="">-- Pilih Nama --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan / Kondisi</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Barang baik, sedang dipinjam, atau perlu servis" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Foto Bukti Audit (Opsional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*" capture="environment">
                        <small class="text-muted">Bisa mengambil foto langsung dari kamera HP</small>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold" style="border-radius: 12px;">
                        <i class="bi bi-check-circle me-2"></i> SUBMIT AUDIT
                    </button>
                </form>
            </div>
        </div>
        
        @if($product->latestAudit)
        <div class="card shadow border-0 mt-3" style="border-radius: 15px;">
            <div class="card-body">
                <h6 class="fw-bold text-muted"><i class="bi bi-clock-history me-2"></i>Audit Terakhir</h6>
                <p class="mb-1"><strong>{{ $product->latestAudit->auditor_name ?? '-' }}</strong> - {{ \Carbon\Carbon::parse($product->latestAudit->audit_date)->format('d/m/Y H:i') }}</p>
                <p class="text-muted small mb-0">{{ $product->latestAudit->notes ?? '-' }}</p>
                @if($product->latestAudit->image_path)
                <div class="mt-2">
                    @if(file_exists(public_path('storage/audit/' . $product->latestAudit->image_path)))
                    <img src="{{ asset('/storage/audit/' . $product->latestAudit->image_path) }}" class="img-fluid rounded" style="max-height: 150px;">
                    @else
                    <span class="badge bg-warning">Foto tidak ditemukan</span>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('auditForm').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Audit',
                text: 'Data audit akan disimpan. Lanjutkan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Submit',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>