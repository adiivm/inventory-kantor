@extends('layouts.app') @section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4 border-0 shadow-sm">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="fw-bold text-primary text-center text-md-start display-4" style="letter-spacing: -2px;">
                            Daftar Assets
                        </h1>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-auto">
                        <a href="{{ url('/product/pdf?' . http_build_query(request()->all())) }}" class="btn btn-danger w-100 px-md-4"> 
                        <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('product.export', request()->all()) }}" class="btn btn-success w-100 px-md-4">
                        📥 Excel
                        </a>
                    </div>
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('product.trash') }}" class="btn btn-outline-secondary w-100">
                        📦 Gudang Archive
                        </a>
                    </div>

                    <div class="col-12 col-md-auto ms-md-auto">
                        <a href="/product/create" class="btn btn-primary w-100 fw-bold px-md-5">
                            + Tambah Barang
                        </a>
                    </div>
                </div>

                <form action="/products" method="GET" class="row g-3 mb-4">                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Berhasil!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </form>
                <form action="/products" method="GET" class="row g-2 mb-4">
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control" placeholder="Cari barang..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <select name="category_id" class="form-select">
                            <option value="">-- Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="division_id" class="form-select">
                            <option value="">-- Divisi --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}" {{ request('division_id') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="held_by_id" class="form-select">
                            <option value="">-- Pemegang --</option>
                            @foreach($held_bies as $hb) {{-- Pastikan pakai $held_bies sesuai Controller --}}
                                <option value="{{ $hb->id }}" {{ request('held_by_id') == $hb->id ? 'selected' : '' }}>
                                    {{ $hb->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="condition" class="form-select">
                            <option value="">-- Kondisi --</option>
                            <option value="ready" {{ request('condition') == 'ready' ? 'selected' : '' }}>🟢 Ready</option>
                            <option value="repair" {{ request('condition') == 'repair' ? 'selected' : '' }}>🟡 Servis</option>
                            <option value="broken" {{ request('condition') == 'broken' ? 'selected' : '' }}>🔴 Rusak</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        @if(request()->anyFilled(['search', 'category_id', 'division_id', 'held_by_id', 'condition']))
                            <a href="{{ url('/products') }}" class="btn btn-secondary">Reset</a>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="tableProduct" class="table table-hover table-bordered w-100 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Image</th>
                                <th width="15%">SKU</th>
                                <th width="25%">Nama Aset</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Stok</th>
                                <th width="20%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($barang as $b)
                            <tr>
                                <td data-label="Identitas">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary">{{ $b->sku }}</span>
                                        <button onclick="printLabel('{{ $b->sku }}', '{{ $b->name }}')" class="btn btn-sm btn-outline-primary py-0 px-2">
                                            🖨️ Print
                                        </button>
                                    </div>
                                </td>
                                <td data-label="Barang" style="width: 25%; min-width: 250px;">
                                    <div class="fw-bold text-primary detail-link" 
                                        onclick="getDetail({{ $b->id }})" 
                                        style="cursor: pointer; text-decoration: underline;">
                                        {{ $b->name }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        <a href="javascript:void(0)" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#historyModal{{ $b->id }}"
                                        class="text-decoration-none text-muted border-bottom-dashed">
                                            Audit : {{ $b->last_audited_at ? \Carbon\Carbon::parse($b->last_audited_at)->format('d/m/y H:i') : 'Belum Pernah' }}
                                        </a>
                                    </small>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                                        <a href="javascript:void(0)" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#historyModal{{ $b->id }}"
                                        class="text-decoration-none text-muted border-bottom-dashed">
                                        Tanggal : {{ $b->last_audited_at ? \Carbon\Carbon::parse($b->last_audited_at)->diffForHumans() : '-' }}
                                        </a>
                                    </small>
                                </td>
                                <td style="cursor: pointer;" onclick="showPreview({{ $b->id }})">
                                    @php
                                        // Cari foto yang is_primary, kalau tidak ada barulah ambil yang paling pertama
                                        $primaryImg = $b->images->where('is_primary', true)->first() ?? $b->images->first();
                                    @endphp

                                    @if($primaryImg)
                                        {{-- Menampilkan foto Primary atau foto Pertama --}}
                                        <img src="{{ asset('storage/products/' . $primaryImg->image_path) }}" 
                                            style="width: 50px; height: 50px; object-fit: cover;" class="rounded shadow-sm border">
                                    @else
                                        {{-- Foto default jika tidak ada gambar sama sekali di database --}}
                                        <img src="{{ asset('images/no-image.png') }}" 
                                            style="width: 50px; height: 50px; object-fit: cover;" class="rounded border">
                                    @endif
                                </td>

                                <td data-label="Kategori">{{ $b->category->name ?? 'Tanpa Kategori' }}</td>
                                <td data-label="Divisi">{{ $b->division->name ?? 'Tanpa Divisi' }}</td>
                                <td>
                                    @if($b->stock_ready > 0)
                                        <span class="badge bg-success">Ready</span>
                                    @elseif($b->stock_repair > 0)
                                        <span class="badge bg-warning text-dark">Servis</span>
                                    @else
                                        <span class="badge bg-danger">Rusak</span>
                                    @endif
                                </td>
                                <td data-label="Pemegang & Lokasi">
                                    {{-- Bagian Pemegang --}}
                                    <div class="fw-bold text-dark">
                                        <i class="fas fa-user-tag small text-primary"></i> 
                                        {{ $b->held_by->name ?? 'Digunakan Bersama' }}
                                    </div>
                                    
                                    {{-- Bagian Lokasi --}}
                                    <div class="small text-muted">
                                        <i class="fas fa-map-marker-alt text-danger"></i> 
                                        {{ $b->location->name ?? 'Lokasi Belum Set' }}
                                    </div>
                                </td>

                                <td data-label="Aksi" class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-info btn-sm text-white" onclick="showLogs({{ $b->id }})">📜 Log</button>
                                        <a href="/product/edit/{{ $b->id }}" class="btn btn-warning btn-sm text-white">✏️ Edit</a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openActionModal({{ $b->id }})">📦 Arc</button>
                                        @if(auth()->user()->role == 'admin') 
                                            <form action="/product/delete/{{ $b->id }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus?')">🗑️ Del</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="historyModal{{ $b->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-primary text-white">
                                            <h6 class="modal-title">📜 Riwayat Audit: {{ $b->name }}</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                                            @php $logs = $b->auditLogs; @endphp
                                            @if($logs && $logs->count() > 0)
                                                @foreach($logs as $log)
                                                    <div class="audit-item pb-2 mb-2 border-bottom">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="badge bg-light text-dark border">
                                                                {{ \Carbon\Carbon::parse($log->audit_date)->format('d/m/Y H:i') }}
                                                            </span>
                                                            <small class="text-muted">{{ $log->auditor_name }}</small>
                                                        </div>
                                                        <p class="mb-0 mt-2 small text-secondary">
                                                            <strong>Notes:</strong> {{ $log->notes ?? '-' }}
                                                        </p>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-center py-3">
                                                    <small class="text-muted">Belum ada riwayat audit.</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="statusModal{{ $b->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header border-0 pb-0">
                                            <h6 class="modal-title fw-bold">Rincian Stok</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="text-center mb-3">
                                                <small class="text-muted d-block">{{ $b->sku }}</small>
                                                <span class="fw-bold">{{ $b->name }}</span>
                                            </div>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                    <span class="text-success fw-bold">Ready</span>
                                                    <span class="badge bg-success rounded-pill">{{ $b->stock_ready }}</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                    <span class="text-warning fw-bold">Servis</span>
                                                    <span class="badge bg-warning text-dark rounded-pill">{{ $b->stock_repair }}</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                    <span class="text-danger fw-bold">Rusak</span>
                                                    <span class="badge bg-danger rounded-pill">{{ $b->stock_broken }}</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                    <span class="text-secondary fw-bold">Habis</span>
                                                    <span class="badge bg-secondary rounded-pill">{{ $b->stock_disposed }}</span>
                                                </li>
                                            </ul>
                                            <div class="bg-light p-2 mt-3 rounded text-center">
                                                <small class="text-muted">Total Stok:</small>
                                                <h5 class="mb-0 fw-bold">{{ $b->stock }} Unit</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @foreach($barang as $b)
                <div class="modal fade" id="modalGambar{{ $b->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 bg-transparent"> <div class="modal-body p-0 text-center">
                                
                                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" style="z-index: 999;"></button>

                                <div id="carouselProduct{{ $b->id }}" class="carousel slide shadow-lg rounded-4 overflow-hidden" data-bs-ride="carousel">
                                    <div class="carousel-inner bg-light">
                                        @forelse($b->images as $index => $img)
                                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                <img src="{{ asset('storage/products/' . $img->image_path) }}" 
                                                    class="d-block w-100" 
                                                    style="height: 450px; object-fit: contain; background-color: #f8f9fa;">
                                            </div>
                                        @empty
                                            <div class="carousel-item active">
                                                <img src="{{ asset('images/no-image.png') }}" class="d-block w-100" style="height: 450px; object-fit: contain;">
                                            </div>
                                        @endforelse
                                    </div>

                                    @if($b->images->count() > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduct{{ $b->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselProduct{{ $b->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                                        </button>
                                    @endif
                                </div>

                                <div class="mt-3 py-2 px-3 bg-white rounded-pill d-inline-block shadow">
                                    <span class="fw-bold text-dark">{{ $b->name }}</span> 
                                    <span class="badge bg-primary ms-2">{{ $b->sku }}</span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="modal fade" id="modalAction" tabindex="-1" aria-labelledby="modalActionLabel" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title" id="modalActionLabel">Opsi Pemindahan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="formAction" method="POST">
                            @csrf
                            <div class="modal-body">
                                <label class="small fw-bold mb-2">Pilih Alasan:</label>
                                <div class="d-grid gap-2">
                                    <button type="submit" name="status" value="jual" class="btn btn-success text-start">
                                        💰 Barang Terjual
                                    </button>
                                    <button type="submit" name="status" value="destroy" class="btn btn-danger text-start">
                                        🔨 Barang Dihancurkan
                                    </button>
                                    <button type="submit" name="status" value="archive" class="btn btn-secondary text-start">
                                        📦 Hanya Arsipkan
                                    </button>
                                </div>
                                <div class="mt-3">
                                    <label class="small fw-bold">Catatan Tambahan (Opsional):</label>
                                    <textarea name="reason" class="form-control form-control-sm" rows="3" placeholder="Contoh: Dijual ke Toko Sebelah..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> Detail Informasi Aset</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="printableArea">
                            <div id="detailContent">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2">Memuat data...</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-success" onclick="printDiv('printableArea')">
                                <i class="fas fa-print me-1"></i> Cetak Detail
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@if(session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}"
        });
    });
</script>
@endif
@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: "{{ session('error') }}",
        confirmButtonColor: '#e74a3b'
    });
</script>
@endif
<script>
    function getDetail(id) {
    // 1. Munculkan modal & efek loading
    $('#modalDetail').modal('show');
    $('#detailContent').html(`
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 text-muted">Membangun detail aset...</div>
        </div>
    `);

    // 2. Tarik data dari server
    $.get(`/product/${id}`, function(data) {
        
        // --- RAKIT CAROUSEL GAMBAR ---
        let carouselIndicators = '';
        let carouselItems = '';
        
        // Cek apakah ada gambar di database
        if (data.images && data.images.length > 0) {
            data.images.forEach((img, index) => {
                let activeClass = index === 0 ? 'active' : '';
                // Field gambar biasanya image_path atau path, kita cover keduanya
                let imgUrl = `/storage/${img.image_path || img.path}`; 
                
                carouselIndicators += `<button type="button" data-bs-target="#carouselProduct" data-bs-slide-to="${index}" class="${activeClass}"></button>`;
                carouselItems += `
                    <div class="carousel-item ${activeClass}">
                        <img src="${imgUrl}" class="d-block w-100 rounded" style="height: 350px; object-fit: cover; border: 1px solid #dee2e6;">
                    </div>`;
            });
        } else {
            // Jika tidak ada gambar, tampilkan gambar default
            carouselItems = `
                <div class="carousel-item active">
                    <img src="/images/no-image.png" class="d-block w-100 rounded" style="height: 350px; object-fit: cover; border: 1px solid #dee2e6;">
                </div>`;
        }

        let carouselHtml = `
            <div id="carouselProduct" class="carousel slide shadow-sm rounded" data-bs-ride="carousel">
                <div class="carousel-indicators">${carouselIndicators}</div>
                <div class="carousel-inner rounded">${carouselItems}</div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduct" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true" style="opacity: 0.7;"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselProduct" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true" style="opacity: 0.7;"></span>
                </button>
            </div>
        `;

        // --- FORMAT DATA (Uang & Tanggal) ---
        let harga = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data.price || 0);
        let tglBeli = data.purchase_date ? new Date(data.purchase_date).toLocaleDateString('id-ID') : '-';
        let tglAudit = data.last_audited_at ? new Date(data.last_audited_at).toLocaleDateString('id-ID') : 'Belum Pernah';
        
        // Laravel merubah huruf besar ke snake_case di JSON, jadi kita jaga-jaga panggil held_by atau heldBy
        let namaPemegang = (data.held_by || data.heldBy)?.name || '-';

        // --- RAKIT HTML DETAIL LENGKAP ---
        let detailHtml = `
            <div class="row g-4">
                <div class="col-md-5">
                    ${carouselHtml}
                </div>
                
                <div class="col-md-7">
                    <h4 class="fw-bold text-primary mb-1">${data.name}</h4>
                    <span class="badge bg-secondary mb-3 fs-6 px-3 py-2 shadow-sm">${data.sku}</span>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Kategori</small>
                            <span class="fw-medium"><i class="bi bi-tag me-1"></i> ${data.category?.name || '-'}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Divisi</small>
                            <span class="fw-medium"><i class="bi bi-building me-1"></i> ${data.division?.name || '-'}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Lokasi</small>
                            <span class="fw-medium"><i class="bi bi-geo-alt me-1"></i> ${data.location?.name || '-'}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Pemegang</small>
                            <span class="fw-medium"><i class="bi bi-person me-1"></i> ${namaPemegang}</span>
                        </div>
                    </div>

                    <div class="card bg-light border-0 mb-3 shadow-sm">
                        <div class="card-body p-2">
                            <small class="text-muted d-block fw-bold mb-2">Rincian Stok (Total: ${data.stock})</small>
                            <div class="d-flex justify-content-between text-center" style="font-size: 0.85rem;">
                                <div><span class="text-success fw-bold fs-5">${data.stock_ready || 0}</span><br>Ready</div>
                                <div><span class="text-warning text-dark fw-bold fs-5">${data.stock_repair || 0}</span><br>Servis</div>
                                <div><span class="text-danger fw-bold fs-5">${data.stock_broken || 0}</span><br>Rusak</div>
                                <div><span class="text-secondary fw-bold fs-5">${data.stock_disposed || 0}</span><br>Musnah</div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm table-borderless" style="font-size: 0.95rem;">
                        <tr><td width="40%" class="text-muted">Harga Beli</td><td class="fw-bold text-success">: ${harga}</td></tr>
                        <tr><td class="text-muted">Tanggal Beli</td><td>: ${tglBeli}</td></tr>
                        <tr><td class="text-muted">Tipe Penggunaan</td><td>: ${data.usage_type || '-'}</td></tr>
                        <tr><td class="text-muted">Terakhir Audit</td><td class="fw-bold">: ${tglAudit}</td></tr>
                    </table>
                </div>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between mt-2">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                <button onclick="printLabel('${data.sku}', '${data.name}')" class="btn btn-primary px-4 shadow-sm">
                    <i class="bi bi-printer me-2"></i> Print Label
                </button>
            </div>
        `;

        // 3. Masukkan HTML ke dalam Modal
        $('#detailContent').html(detailHtml);
        
        // 4. Inisialisasi ulang fungsi Carousel Bootstrap agar panahnya bisa diklik
        var myCarousel = document.querySelector('#carouselProduct');
        if(myCarousel) {
            new bootstrap.Carousel(myCarousel);
        }

    }).fail(function(xhr) {
        $('#detailContent').html('<div class="alert alert-danger text-center">Wah, gagal memuat detail data. Silakan coba lagi.</div>');
    });
}

    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload(); // Reload agar event JS tidak hilang setelah print
    }
</script>
<script>
function openActionModal(id) {
    // Set action URL pada form secara dinamis
    const form = document.getElementById('formAction');
    form.action = `/product/archive/${id}`; // Sesuaikan dengan route archive Anda
    
    // Munculkan modal
    var actionModal = new bootstrap.Modal(document.getElementById('modalAction'));
    actionModal.show();
}
</script>
<script>
    var table = $('#tableProduct').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('product.index') }}",
            data: function (d) {
                // Ini agar filter dropdown Mas Bro terkirim ke Controller
                d.category_id = $('#filter_category').val();
                d.division_id = $('#filter_division').val();
                d.filter = "{{ request('filter') }}"; // Menangkap filter dari dashboard
                d.condition = $('#filter_condition').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex' },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'sku', name: 'sku' },
            { data: 'name', name: 'name' },
            { data: 'category.name', name: 'category.name' },
            { data: 'stock', name: 'stock' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Jalankan reload tabel saat dropdown filter diganti
    $('#filter_category, #filter_division, #filter_condition').change(function(){
        table.draw();
    });
</script>

@endsection