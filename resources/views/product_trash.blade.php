@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4 border-0 shadow-sm">
                
                <div class="row mb-4 align-items-center">
                    <div class="col-md-8">
                        <h1 class="fw-bold text-secondary text-center text-md-start display-4" style="letter-spacing: -2px;">
                            Arsip & Riwayat Barang
                        </h1>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ url('/products') }}" class="btn btn-outline-primary fw-bold px-4">
                            <i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar Aktif
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tableArchive" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="25%">NAMA ASSETS</th>
                                <th width="15%">STATUS KELUAR</th>
                                <th width="25%">KETERANGAN LOG</th>
                                <th width="15%">STATUS STOK</th>
                                <th width="20%" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($barang as $b) 
                            <tr>
                                <td>
                                    <strong class="d-block text-primary fs-6">{{ $b->name }}</strong>
                                    <span class="badge bg-{{ $b->warranty_color }} shadow-sm">{{ $b->sku }}</span>
                                </td>
                                <td>
                                    @if($b->is_active == 'jual')
                                        <span class="badge bg-success px-3 py-2 shadow-sm"><i class="bi bi-cash-coin me-1"></i> TERJUAL</span>
                                    @elseif($b->is_active == 'destroy')
                                        <span class="badge bg-danger px-3 py-2 shadow-sm"><i class="bi bi-hammer me-1"></i> DIHANCURKAN</span>
                                    @elseif($b->is_active == 'archive')
                                        <span class="badge bg-secondary px-3 py-2 shadow-sm"><i class="bi bi-box-seam me-1"></i> DIARSIPKAN</span>
                                    @else
                                        <span class="badge bg-dark px-3 py-2 shadow-sm">{{ strtoupper($b->is_active) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted text-wrap" style="max-width: 250px; display: inline-block;">
                                        {{ $b->logs()->latest()->first()->description ?? 'Tidak ada catatan spesifik.' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="small" style="font-size: 0.85rem;">
                                        <span class="text-success">Ready: <strong>{{ $b->stock_ready }}</strong></span><br>
                                        <span class="text-warning text-dark">Servis: <strong>{{ $b->stock_repair }}</strong></span><br>
                                        <span class="text-danger">Rusak: <strong>{{ $b->stock_broken }}</strong></span>
                                    </div>
                                    <hr class="my-1 border-secondary">
                                    <div class="fw-bold fs-6">
                                        Total: {{ $b->stock_ready + $b->stock_repair + $b->stock_broken }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-info btn-detail" data-id="{{ $b->id }}" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning btn-logs" data-id="{{ $b->id }}" title="Log History">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-sm btn-success btn-restore ms-1" data-id="{{ $b->id }}" title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>

                                    @can('admin-only')
                                    <button type="button" class="btn btn-sm btn-danger btn-delete ms-1" data-id="{{ $b->id }}" title="Hapus Permanen">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>Detail Asset Arsip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Mengambil data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLogAset" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-clock-history"></i> Rekam Jejak Aset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="logTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="system-tab" data-bs-toggle="tab" data-bs-target="#system-logs" type="button">
                            <i class="bi bi-cpu"></i> History System
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit-logs" type="button">
                            <i class="bi bi-qr-code-scan"></i> Audit Fisik (QR)
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="logTabsContent">
                    <div class="tab-pane fade show active" id="system-logs">
                        <div id="content-system">Memuat data...</div>
                    </div>
                    <div class="tab-pane fade" id="audit-logs">
                        <div id="content-audit">Memuat data...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="printSection" style="display: none;"></div>

@endsection

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        });
    </script>
@endif

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTables Client-Side
        var table = $('#tableArchive').DataTable({
            language: {
                search: "",
                searchPlaceholder: "Cari arsip..."
            },
            ordering: true,
            // Opsional: urutkan dari yang terbaru di-arsip (asumsi id ada di kolom tertentu, misal tidak kita set dulu)
        });

        // 2. Event Delegation: Tombol Detail
        $('#tableArchive').on('click', '.btn-detail', function() {
            var id = $(this).data('id');
            getDetail(id);
        });

        // 3. Event Delegation: Tombol Logs
        $('#tableArchive').on('click', '.btn-logs', function() {
            var id = $(this).data('id');
            showLogs(id);
        });

        // 4. Event Delegation: Tombol Restore (Menggantikan Form konvensional)
        $('#tableArchive').on('click', '.btn-restore', function() {
            var id = $(this).data('id');
            restoreProduct(id);
        });

        // 5. Event Delegation: Tombol Delete Permanen
        $('#tableArchive').on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            forceDelete(id);
        });
    });

    // ==========================================
    // FUNGSI-FUNGSI JAVASCRIPT (DI-COPY DARI PRODUCTS)
    // ==========================================

    // A. Fungsi Detail & Print Format
    function getDetail(id) {
        $('#modalDetail').modal('show');
        $('#detailContent').html(`
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted">Membangun detail aset arsip...</div>
            </div>
        `);

        // Asumsi Endpoint /product/{id} bisa mengambil data meskipun statusnya archive
        $.get(`/product/${id}`, function(data) {
            var harga = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data.price || 0);
            var tglBeli = data.purchase_date ? new Date(data.purchase_date).toLocaleDateString('id-ID') : '-';
            var tglGaransi = data.warranty_expiry_date ? new Date(data.warranty_expiry_date).toLocaleDateString('id-ID') : 'Tidak Ada / Habis';
            var warnaBadge = data.warranty_color || 'secondary'; // Ambil warna dari Controller
            var tglAudit = data.last_audited_at ? new Date(data.last_audited_at).toLocaleDateString('id-ID') : 'Belum Pernah';
            var namaPemegang = (data.held_by || data.heldBy)?.name || '-';
            
            var carouselIndicators = '';
            var carouselItems = '';
            var gridPhotosPrint = '<div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-start;">';

            if (data.images && data.images.length > 0) {
                data.images.forEach((img, index) => {
                    let activeClass = index === 0 ? 'active' : '';
                    let imgUrl = `/storage/products/${img.image_path || img.path}`; 
                    let fullImgUrl = window.location.origin + imgUrl;
                    
                    carouselIndicators += `<button type="button" data-bs-target="#carouselProduct" data-bs-slide-to="${index}" class="${activeClass}"></button>`;
                    carouselItems += `
                        <div class="carousel-item ${activeClass}">
                            <img src="${imgUrl}" class="d-block w-100 rounded" style="height: 350px; object-fit: cover; border: 1px solid #dee2e6;">
                        </div>`;
                    gridPhotosPrint += `
                        <div style="width: 31.3%; border: 1px solid #ddd; padding: 3px; border-radius: 4px; margin-bottom: 5px; box-sizing: border-box;">
                            <img src="${fullImgUrl}" style="width: 100%; height: 130px; object-fit: cover; display: block;">
                        </div>`;
                });
            } else {
                carouselItems = `<div class="carousel-item active"><img src="/images/no-image.png" class="d-block w-100 rounded" style="height: 350px; object-fit: cover;"></div>`;
                gridPhotosPrint += `<div style="width: 100%; text-align: center; padding: 20px; border: 1px solid #ddd; color: #666;">Tidak ada foto produk</div>`;
            }
            gridPhotosPrint += '</div>';

            let detailHtml = `
                <div class="row g-4">
                    <div class="col-md-5">
                        <div id="carouselProduct" class="carousel slide shadow-sm rounded no-print" data-bs-ride="carousel">
                            <div class="carousel-indicators">${carouselIndicators}</div>
                            <div class="carousel-inner rounded">${carouselItems}</div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselProduct" data-bs-slide="prev"><span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span></button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselProduct" data-bs-slide="next"><span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span></button>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h4 class="fw-bold text-primary mb-1">${data.name}</h4>
                        <span class="badge bg-${warnaBadge} mb-3 fs-6 px-3 py-2 shadow-sm">${data.sku}</span>
                        
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
                                <small class="text-muted d-block">Pemegang Terakhir</small>
                                <span class="fw-medium"><i class="bi bi-person me-1"></i> ${namaPemegang}</span>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mb-3 shadow-sm">
                            <div class="card-body p-2">
                                <small class="text-muted d-block fw-bold mb-2">Rincian Stok Arsip (Total: ${data.stock})</small>
                                <div class="d-flex justify-content-between text-center" style="font-size: 0.85rem;">
                                    <div><span class="text-success fw-bold fs-5">${data.stock_ready || 0}</span><br>Ready</div>
                                    <div><span class="text-warning text-dark fw-bold fs-5">${data.stock_repair || 0}</span><br>Servis</div>
                                    <div><span class="text-danger fw-bold fs-5">${data.stock_broken || 0}</span><br>Rusak</div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-sm table-borderless" style="font-size: 0.95rem;">
                            <tr><td width="40%" class="text-muted">Harga Beli</td><td class="fw-bold text-success">: ${harga}</td></tr>
                            <tr><td class="text-muted">Tanggal Beli</td><td>: ${tglBeli}</td></tr>
                            <tr><td class="text-muted">Masa Garansi</td><td class="fw-bold text-${warnaBadge === 'warning text-dark' ? 'warning' : warnaBadge}">: ${tglGaransi}</td></tr>
                            <tr><td class="text-muted">Tipe Penggunaan</td><td>: ${data.usage_type || '-'}</td></tr>
                            <tr><td class="text-muted">Terakhir Audit</td><td class="fw-bold">: ${tglAudit}</td></tr>
                        </table>
                    </div>
                </div>
                
                <hr class="no-print">
                
                <div class="d-flex justify-content-between mt-2 no-print">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                    <div>
                        <button type="button" onclick="printModal()" class="btn btn-success px-4">
                            <i class="bi bi-printer me-1"></i> Print A4
                        </button>
                    </div>
                </div>
            `;

            $('#detailContent').html(detailHtml);

            // Print Layout
            let printLayout = `
                <div style="font-family: sans-serif; color: #000; padding: 10px;">
                    <div style="text-align: center; border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px;">
                        <h2 style="margin: 0;">LAPORAN DETAIL ASET (ARSIP)</h2>
                        <p style="margin: 5px 0;">ID Asset: ${data.sku}</p>
                    </div>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
                        <tr>
                            <td style="width: 20%; padding: 5px; font-weight: bold; border: 1px solid #ddd;">Nama Barang</td>
                            <td style="width: 30%; padding: 5px; border: 1px solid #ddd;">${data.name}</td>
                            <td style="width: 20%; padding: 5px; font-weight: bold; border: 1px solid #ddd;">Kategori</td>
                            <td style="width: 30%; padding: 5px; border: 1px solid #ddd;">${data.category?.name || '-'}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Divisi</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${data.division?.name || '-'}</td>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Lokasi</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${data.location?.name || '-'}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Pemegang Terakhir</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${namaPemegang}</td>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Harga Beli</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${harga}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Tanggal Beli | Masa Garansi</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${tglBeli} | ${tglGaransi}</td>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Status Stok</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">Ready: ${data.stock_ready} | Servis: ${data.stock_repair} | Rusak: ${data.stock_broken}</td>
                        </tr>
                    </table>
                    <h4 style="border-left: 5px solid #333; padding-left: 10px; margin-bottom: 10px;">DOKUMENTASI FOTO</h4>
                    ${gridPhotosPrint}
                    <div style="margin-top: 30px; text-align: right; font-size: 11px;">
                        Dicetak pada: ${new Date().toLocaleString('id-ID')}
                    </div>
                </div>
            `;

            $('#printSection').html(printLayout);

            var myCarousel = document.querySelector('#carouselProduct');
            if(myCarousel) { new bootstrap.Carousel(myCarousel); }

        }).fail(function() {
            $('#detailContent').html('<div class="alert alert-danger text-center">Gagal memuat data.</div>');
        });
    }

    // B. Fungsi Tampil Log (Sama Persis)
    function showLogs(id) {
        $('#modalLogAset').modal('show');
        $('#content-system, #content-audit').html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm"></div> Memuat...</div>');
        $.get(`/product/${id}/all-logs`, function(response) {
            $('#content-system').html(response.system_html);
            $('#content-audit').html(response.audit_html);
        }).fail(function() {
            Swal.fire('Error', 'Gagal mengambil data log', 'error');
        });
    }

    // C. Fungsi Restore (Di-upgrade dengan SweetAlert)
    function restoreProduct(id) {
        Swal.fire({
            title: 'Restore Barang?',
            text: "Barang ini akan kembali muncul di Daftar Aset Aktif.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Restore!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.action = `/product/restore/${id}`; 
                form.method = 'POST';
                form.innerHTML = `@csrf`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // D. Fungsi Hapus Permanen (Sama Persis dengan Products)
    function forceDelete(id) {
        Swal.fire({
            title: 'Hapus Permanen?',
            text: "Peringatan: Data ini akan dihapus total dari database dan tidak bisa dikembalikan!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dd4b39',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Selamanya!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.action = `/product/delete/${id}`; 
                form.method = 'POST';
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // E. Fungsi Print A4
    function printModal() {
        let printContents = document.getElementById("printSection").innerHTML;
        if (!printContents || printContents.trim() === '') {
            alert('Data belum siap, silakan tunggu sebentar...');
            return;
        }

        let printFrame = document.createElement('iframe');
        printFrame.name = "print_frame";
        printFrame.style.position = "absolute";
        printFrame.style.width = "0px";
        printFrame.style.height = "0px";
        printFrame.style.border = "none";
        document.body.appendChild(printFrame);

        let doc = printFrame.contentWindow.document;
        doc.open();
        doc.write(`
            <html>
            <head>
                <title>Print Detail Aset Arsip</title>
                <style>
                    @page { size: A4; margin: 15mm; }
                    body { font-family: Arial, sans-serif; color: black; background: white; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
                    td { border: 1px solid #ddd; padding: 6px; }
                    img { page-break-inside: avoid; } 
                </style>
            </head>
            <body>
                ${printContents}
            </body>
            </html>
        `);
        doc.close();

        setTimeout(function() {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
            setTimeout(function() {
                document.body.removeChild(printFrame);
            }, 1000);
        }, 500);
    }
</script>
@endpush