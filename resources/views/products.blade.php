@extends('layouts.app') 

@push('styles')
<style>
    /* --- TABLET: SEMBUNYIKAN KOLOM KURANG PENTING --- */
    @media (max-width: 1024px) and (min-width: 768px) {
        #tableProduct th:nth-child(4),
        #tableProduct td:nth-child(4),
        #tableProduct th:nth-child(7),
        #tableProduct td:nth-child(7) {
            display: none;
        }
    }

    /* --- MOBILE: CARD LAYOUT --- */
    @media (max-width: 767px) {
        .table-responsive {
            overflow: visible !important;
        }
        #tableProduct {
            width: 100% !important;
        }
        #tableProduct thead {
            display: none;
        }
        #tableProduct tbody tr {
            display: block;
            margin-bottom: 1rem;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 12px;
        }
        #tableProduct tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }
        #tableProduct tbody td:last-child {
            border-bottom: none;
        }
        #tableProduct tbody td::before {
            content: attr(data-label);
            font-size: 0.75rem;
            font-weight: 600;
            color: #a3adc2;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            flex-shrink: 0;
            margin-right: 10px;
        }
        #tableProduct tbody td[data-label="AKSI"]::before,
        #tableProduct tbody td[data-label=""]::before {
            display: none;
        }
        #tableProduct td:nth-child(1),
        #tableProduct td:nth-child(5),
        #tableProduct td:nth-child(7) {
            display: none;
        }
        #tableProduct td:nth-child(4) img {
            width: 50px !important;
            height: 35px !important;
            border-radius: 6px;
        }
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 4px;
            width: 100%;
        }
        .action-buttons .btn {
            padding: 5px 8px;
            font-size: 0.75rem;
        }
        .action-buttons .btn i {
            font-size: 0.85rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4 border-0 shadow-sm">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="fw-bold text-dark">
                            <i class="bi bi-box-seam-fill me-2"></i>Assets
                        </h2>
                        <p class="text-muted mb-0">Manage and track all company inventory assets</p>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('product.import_template') }}" class="btn btn-outline-success w-100 fw-bold px-md-3">
                            <i class="bi bi-download me-2"></i> <span class="d-none d-md-inline">Download Template</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-auto">
                        <button class="btn btn-outline-primary w-100 fw-bold px-md-3" data-bs-toggle="modal" data-bs-target="#modalImport">
                            <i class="bi bi-upload me-2"></i> <span class="d-none d-md-inline">Import Excel</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-auto">
                        <button class="btn btn-success w-100 fw-bold px-md-3" onclick="bulkPrintLabels()" id="btnBulkPrint" disabled>
                            <i class="bi bi-printer me-2"></i> <span class="d-none d-md-inline">Print Selected</span> (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                    <div class="col-6 col-md-auto ms-md-auto">
                        <a href="/product/create" class="btn btn-primary w-100 fw-bold px-md-5">
                            + <span class="d-none d-md-inline">Tambah Barang</span>
                        </a>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-2 col-6">
                        <select id="filter_category" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select id="filter_division" class="form-select form-select-sm">
                            <option value="">Semua Divisi</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select id="filter_held_by" class="form-select form-select-sm">
                            <option value="">Semua Pemegang</option>
                            @foreach($held_bies as $hb)
                                <option value="{{ $hb->id }}">{{ $hb->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select id="filter_location" class="form-select form-select-sm">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select id="filter_condition" class="form-select form-select-sm">
                            <option value="">Semua Kondisi</option>
                            <option value="ready">Ready</option>
                            <option value="repair">Servis</option>
                            <option value="broken">Rusak</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <button id="btnReset" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tableProduct" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%"><input type="checkbox" id="selectAll"></th>
                                <th width="10%">SKU</th>
                                <th width="20%">NAMA ASSETS</th>
                                <th width="10%">IMAGE</th>
                                <th width="10%">KATEGORI</th>
                                <th width="10%">DIVISI</th>
                                <th width="10%">SUPPLIER</th>
                                <th width="10%">KONDISI</th>
                                <th width="15%">PEMEGANG (POSISI)</th>
                                <th width="10%">AKSI</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDynamic" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="dynamicTitle">Detail Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div id="carouselContainer"></div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>SKU</th><td id="detSku"></td></tr>
                            <tr><th>Nama</th><td id="detName"></td></tr>
                            <tr><th>Stok</th><td id="detStock"></td></tr>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>Detail Asset</h5>
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

<div class="modal fade" id="modalAudit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-white"><i class="bi bi-clock-history me-2"></i>History Audit / Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="auditContent">
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

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Import Data dari Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formImport" action="{{ route('product.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Unduh template terlebih dahulu, isi data sesuai format, lalu upload file tersebut.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i> Import Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: {!! json_encode(session('success')) !!},
        showConfirmButton: false,
        timer: 4000
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: {!! json_encode(session('error')) !!},
        showConfirmButton: true,
        timer: 5000
    });
    @endif
</script>
<script>
    $(document).ready(function() {
        // AJAX Import Excel
        $('#formImport').on('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('product.import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#formImport').find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Meng-import...');
                },
                success: function(response) {
                    $('#modalImport').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Import berhasil!',
                        showConfirmButton: false,
                        timer: 4000
                    }).then(() => {
                        window.location.href = "{{ route('product.index') }}";
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat import.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: errorMessage,
                        showConfirmButton: true
                    });
                },
                complete: function() {
                    $('#formImport').find('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Import Data');
                }
            });
        });

        // 1. Inisialisasi DataTable
        var table = $('#tableProduct').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('product.index') }}",
                data: function(d) {
                    let urlParams = new URLSearchParams(window.location.search);
                    d.warranty_status = urlParams.get('warranty_status');
                    d.condition = urlParams.get('condition') || $('#filter_condition').val();
                    d.search_sku = urlParams.get('search_sku');
                    d.category_id = $('#filter_category').val();
                    d.division_id = $('#filter_division').val();
                    d.held_by_id = $('#filter_held_by').val();
                    d.location_id = $('#filter_location').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'id', orderable: false, searchable: false },
                { data: 'sku_custom', name: 'sku' },
                { data: 'asset_info', name: 'name' },
                { data: 'image_thumb', name: 'id', orderable: false },
                { data: 'category.name', name: 'category.name', defaultContent: '-' },
                { data: 'division.name', name: 'division.name', defaultContent: '-' },
                { data: 'supplier_name', name: 'supplier.name', defaultContent: '-' },
                
                // --- PASTIKAN BARIS INI MENGGUNAKAN condition_badge ---
                { data: 'condition_badge', name: 'condition' }, 
                
                { data: 'holder_info', name: 'heldBy.name', defaultContent: '-' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            // --- TAMBAHKAN BAGIAN INI UNTUK TAMPILAN MOBILE ---
            createdRow: function(row, data, dataIndex) {
                $(row).find('td:eq(0)').attr('data-label', '');
                $(row).find('td:eq(1)').attr('data-label', 'SKU');
                $(row).find('td:eq(2)').attr('data-label', 'NAMA');
                $(row).find('td:eq(3)').attr('data-label', '');
                $(row).find('td:eq(4)').attr('data-label', 'KATEGORI');
                $(row).find('td:eq(5)').attr('data-label', 'DIVISI');
                $(row).find('td:eq(6)').attr('data-label', 'SUPPLIER');
                $(row).find('td:eq(7)').attr('data-label', 'KONDISI');
                $(row).find('td:eq(8)').attr('data-label', 'PEMEGANG');
                $(row).find('td:eq(9)').attr('data-label', 'AKSI');
                
                let actionHtml = $(row).find('td:eq(9)').html();
                $(row).find('td:eq(9)').html('<div class="action-buttons">' + actionHtml + '</div>');
            },
            // ---------------------------------------------------
            // Styling tambahan agar mirip gambar
            language: {
                search: "",
                searchPlaceholder: "Cari barang..."
            }
        });

        // 2. Fungsi Filter (Tetap Aktif)
        $('#filter_category, #filter_division, #filter_held_by, #filter_location, #filter_condition').change(function(){
            table.draw();
        });

        // 3. Tombol Reset
        $('#btnReset').click(function() {
            $('select').val('');
            table.draw();
        });

        // 4. EVENT DELEGATION (PENTING!)
        // Karena baris tabel baru muncul setelah AJAX, kita pakai cara ini:
        
        // Klik Tombol Detail
        $('#tableProduct').on('click', '.btn-detail', function() {
            var id = $(this).data('id');
            getDetail(id); // Memanggil fungsi detail Mas Bro
        });

        // Klik Tombol Logs/Audit
        $('#tableProduct').on('click', '.btn-logs', function() {
            var id = $(this).data('id');
            showLogs(id); // Fungsi yang ada di App.blade
        });
    });

    function getDetail(id) {
        $('#modalDetail').modal('show');
        $('#detailContent').html(`
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted">Membangun detail aset...</div>
            </div>
        `);

        $.get(`/product/${id}`, function(data) {
            // 1. SIAPKAN DATA
            var harga = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data.price || 0);
            var tglBeli = data.purchase_date ? new Date(data.purchase_date).toLocaleDateString('id-ID') : '-';
            var tglGaransi = data.warranty_expiry_date ? new Date(data.warranty_expiry_date).toLocaleDateString('id-ID') : 'Tidak Ada / Habis';
            var warnaBadge = data.warranty_color || 'secondary'; // Ambil warna dari Controller
            var tglAudit = data.last_audited_at ? new Date(data.last_audited_at).toLocaleDateString('id-ID') : 'Belum Pernah';
            var namaPemegang = (data.held_by || data.heldBy)?.name || '-';
            
            var carouselIndicators = '';
            var carouselItems = '';
            var gridPhotosPrint = '<div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-start;">';

            // 2. PROSES FOTO (Carousel & Print Grid 3x3)
            if (data.images && data.images.length > 0) {
                data.images.forEach((img, index) => {
                    let activeClass = index === 0 ? 'active' : '';
                    let imgUrl = `/storage/products/${img.image_path || img.path}`; 
                    let fullImgUrl = window.location.origin + imgUrl;
                    
                    // ISI CAROUSEL MODAL (Ini yang tadi ketinggalan)
                    carouselIndicators += `<button type="button" data-bs-target="#carouselProduct" data-bs-slide-to="${index}" class="${activeClass}"></button>`;
                    carouselItems += `
                        <div class="carousel-item ${activeClass}">
                            <img src="${imgUrl}" class="d-block w-100 rounded" style="height: 350px; object-fit: cover; border: 1px solid #dee2e6;">
                        </div>`;

                    // ISI GRID PRINT
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

            // 3. RAKIT HTML MODAL (Tampilan Layar Sesuai Keinginan Mas Bro)
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
                                <small class="text-muted d-block">Pemegang</small>
                                <span class="fw-medium"><i class="bi bi-person me-1"></i> ${namaPemegang}</span>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mb-3 shadow-sm">
                            <div class="card-body p-2">
                                <small class="text-muted d-block fw-bold mb-2">Kondisi</small>
                                <div class="d-flex justify-content-center">
                                    ${getConditionBadge(data.condition)}
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 0.95rem;">
                            <div class="d-flex py-1 border-bottom border-light"><span class="text-muted" style="width: 110px; flex-shrink: 0;">Supplier</span><span class="fw-bold">: ${data.supplier?.name || '-'}</span></div>
                            <div class="d-flex py-1 border-bottom border-light"><span class="text-muted" style="width: 110px; flex-shrink: 0;">Harga Beli</span><span class="fw-bold text-success">: ${harga}</span></div>
                            <div class="d-flex py-1 border-bottom border-light"><span class="text-muted" style="width: 110px; flex-shrink: 0;">Tanggal Beli</span><span>: ${tglBeli}</span></div>
                            <div class="d-flex py-1 border-bottom border-light"><span class="text-muted" style="width: 110px; flex-shrink: 0;">Masa Garansi</span><span class="fw-bold text-${warnaBadge === 'warning text-dark' ? 'warning' : warnaBadge}">: ${tglGaransi}</span></div>
                            <div class="d-flex py-1 border-bottom border-light"><span class="text-muted" style="width: 110px; flex-shrink: 0;">Tipe Penggunaan</span><span>: ${data.usage_type || '-'}</span></div>
                            <div class="d-flex py-1"><span class="text-muted" style="width: 110px; flex-shrink: 0;">Terakhir Audit</span><span class="fw-bold">: ${tglAudit}</span></div>
                        </div>
                    </div>
                </div>
                
                <hr class="no-print">
                
                <div class="d-flex justify-content-between mt-2 no-print">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                    <div>
                        <button onclick="printLabel('${data.sku}', '${data.name}')" class="btn btn-primary px-4 shadow-sm me-2">
                            <i class="bi bi-printer me-2"></i> Print Label
                        </button>
                        <button type="button" onclick="printModal()" class="btn btn-success px-4">
                            <i class="bi bi-printer me-1"></i> Print A4
                        </button>
                    </div>
                </div>
            `;

            $('#detailContent').html(detailHtml);

            // 4. RAKIT HTML KHUSUS PRINT
            let printLayout = `
                <div style="font-family: sans-serif; color: #000; padding: 10px;">
                    <div style="text-align: center; border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px;">
                        <h2 style="margin: 0;">LAPORAN DETAIL ASET</h2>
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
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Pemegang</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${namaPemegang}</td>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Supplier</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${data.supplier?.name || '-'}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Tanggal Beli</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${tglBeli}</td>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Harga Beli</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${harga}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Masa Garansi</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${tglGaransi}</td>
                            <td style="padding: 5px; font-weight: bold; border: 1px solid #ddd;">Kondisi</td>
                            <td style="padding: 5px; border: 1px solid #ddd;">${getConditionBadge(data.condition)}</td>
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

            // Aktifkan Carousel
            var myCarousel = document.querySelector('#carouselProduct');
            if(myCarousel) { new bootstrap.Carousel(myCarousel); }

        }).fail(function() {
            $('#detailContent').html('<div class="alert alert-danger text-center">Gagal memuat data.</div>');
        });
    }

    // FUNGSI AUDIT LOGS
    function showLogs(id) {
        $('#modalLogAset').modal('show');
        
        // Reset tampilan ke loading
        $('#content-system, #content-audit').html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm"></div> Memuat...</div>');

        $.get(`/product/${id}/all-logs`, function(response) {
            $('#content-system').html(response.system_html);
            $('#content-audit').html(response.audit_html);
        }).fail(function() {
            Swal.fire('Error', 'Gagal mengambil data log', 'error');
        });
    }

    // 1. Fungsi Archive dengan Alasan
    function archiveProduct(id) {
        Swal.fire({
            title: 'Pindahkan ke Archive?',
            html: `
                <div class="text-start">
                    <label class="form-label fw-bold">Kategori Alasan:</label>
                    <select id="swal-status" class="form-select mb-3">
                        <option value="archive">Arsip Rutin</option>
                        <option value="rusak">Barang Rusak</option>
                        <option value="hilang">Barang Hilang</option>
                        <option value="jual">Dijual/Lelang</option>
                    </select>
                    <label class="form-label fw-bold">Catatan Spesifik (Wajib):</label>
                    <textarea id="swal-reason" class="form-control" placeholder="Contoh: Rusak di bagian layar, tidak bisa nyala..." rows="3"></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Simpan ke Archive',
            preConfirm: () => {
                const status = document.getElementById('swal-status').value;
                const reason = document.getElementById('swal-reason').value;
                if (!reason) {
                    Swal.showValidationMessage('Mas Bro, alasan spesifik wajib diisi!');
                }
                return { status: status, reason: reason };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                // PASTIKAN URL INI SAMA DENGAN ROUTE DI WEB.PHP
                form.action = `/product/archive/${id}`; 
                form.method = 'POST';
                form.innerHTML = `
                    @csrf
                    <input type="hidden" name="status" value="${result.value.status}">
                    <input type="hidden" name="reason" value="${result.value.reason}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

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
                // Buat form dinamis
                let form = document.createElement('form');
                // SESUAIKAN DENGAN ROUTE BARU MAS BRO: /product/delete/{id}
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

    // Bulk Print Labels
    let selectedProducts = [];

    $('#selectAll').change(function() {
        $('.product-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedProducts();
    });

    function updateSelectedProducts() {
        selectedProducts = [];
        $('.product-checkbox:checked').each(function() {
            selectedProducts.push($(this).val());
        });
        $('#selectedCount').text(selectedProducts.length);
        $('#btnBulkPrint').prop('disabled', selectedProducts.length === 0);
    }

    $(document).on('change', '.product-checkbox', function() {
        updateSelectedProducts();
        const allChecked = $('.product-checkbox').length === $('.product-checkbox:checked').length;
        $('#selectAll').prop('checked', allChecked);
    });

    function bulkPrintLabels() {
        if (selectedProducts.length === 0) {
            Swal.fire('Peringatan', 'Pilih minimal satu produk terlebih dahulu!', 'warning');
            return;
        }

        let form = document.createElement('form');
        form.action = '{{ route("product.bulk_print_labels") }}';
        form.method = 'POST';
        form.target = '_blank';

        let csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        selectedProducts.forEach(function(id) {
            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'product_ids[]';
            idInput.value = id;
            form.appendChild(idInput);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    function printModal() {
        // 1. Ambil isi HTML dari wadah print yang sudah disiapkan di getDetail
        let printContents = document.getElementById("printSection").innerHTML;
        
        // Pastikan data tidak kosong
        if (!printContents || printContents.trim() === '') {
            alert('Data belum siap, silakan tunggu sebentar...');
            return;
        }

        // 2. Buat "Jendela Rahasia" (Iframe)
        let printFrame = document.createElement('iframe');
        printFrame.name = "print_frame";
        printFrame.style.position = "absolute";
        printFrame.style.width = "0px";
        printFrame.style.height = "0px";
        printFrame.style.border = "none";
        
        // Masukkan Iframe ke dalam body
        document.body.appendChild(printFrame);

        // 3. Tulis dokumen ke dalam Iframe tersebut
        let doc = printFrame.contentWindow.document;
        doc.open();
        doc.write(`
            <html>
            <head>
                <title>Print Detail Aset</title>
                <style>
                    /* CSS Khusus hanya untuk hasil Print A4 */
                    @page { size: A4; margin: 15mm; }
                    body { 
                        font-family: Arial, sans-serif; 
                        color: black; 
                        background: white;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-bottom: 20px; 
                        font-size: 13px; 
                    }
                    td { border: 1px solid #ddd; padding: 6px; }
                    img { page-break-inside: avoid; } /* Biar foto gak kepotong */
                </style>
            </head>
            <body>
                ${printContents}
            </body>
            </html>
        `);
        doc.close();

        // 4. Fokuskan browser ke Iframe dan lakukan Print
        // Kita beri jeda 500ms agar gambar sempat ter-load sebelum di-print
        setTimeout(function() {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
            
            // Hapus Iframe setelah selesai print agar memori tidak penuh
            setTimeout(function() {
                document.body.removeChild(printFrame);
            }, 1000);
        }, 500);
    }
</script>
@endpush