@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 768px) {
        #tableArchive tbody tr {
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 15px;
            border: 1px solid #e9edf7;
            border-radius: 10px;
            background: #fff;
        }
        
        /* Hide table headers on mobile */
        #tableArchive thead { display: none; }
        
        /* Convert td to flex with label */
        #tableArchive td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center;
            border: none !important;
            padding: 8px 5px !important;
            border-bottom: 1px dashed #e9edf7 !important;
        }
        
        #tableArchive td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #a3adc2;
            font-size: 0.7rem;
            text-transform: uppercase;
            flex-shrink: 0;
            margin-right: 10px;
        }
        
        /* Specific column adjustments */
        #tableArchive td:nth-child(1)::before { content: 'NAMA ASSET'; }
        #tableArchive td:nth-child(2)::before { content: 'STATUS KELUAR'; }
        #tableArchive td:nth-child(3)::before { content: 'KETERANGAN'; }
        #tableArchive td:nth-child(4)::before { content: 'STOK'; }
        #tableArchive td:nth-child(5)::before { content: 'AKSI'; }
        
        /* Action column - hide label and make buttons horizontal */
        #tableArchive td:last-child {
            display: flex !important;
            flex-direction: row !important;
            justify-content: flex-end !important;
            gap: 5px;
            padding-top: 15px !important;
            border-top: 1px dashed #e9edf7 !important;
            margin-top: 10px;
            border-bottom: none !important;
        }
        
        #tableArchive td:last-child::before {
            display: none;
        }
        
        .action-buttons-archive {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: flex-end;
        }
        
        .action-buttons-archive .btn {
            padding: 5px 8px;
            font-size: 0.8rem;
        }
        
        /* Stock info on mobile */
        #tableArchive td:nth-child(4) {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        #tableArchive td:nth-child(4)::before {
            margin-bottom: 5px;
        }
        #tableArchive td:nth-child(4) .small {
            display: flex;
            gap: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4 border-0 shadow-sm">
                
                <div class="row mb-4 align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold text-secondary">
                            <i class="bi bi-archive-fill me-2"></i>Archive & History
                        </h2>
                        <p class="text-muted mb-0">Archived items or removed from inventory</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ url('/products') }}" class="btn btn-outline-primary fw-bold px-4">
                            <i class="bi bi-arrow-left me-2"></i> Back to Active List
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
                            <option value="disposed">Dibuang</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <button id="btnReset" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tableArchive" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="25%">NAMA ASSETS</th>
                                <th width="15%">STATUS KELUAR</th>
                                <th width="25%">KETERANGAN LOG</th>
                                <th width="15%">KONDISI</th>
                                <th width="20%" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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

@push('scripts')
<script>
    @if(session('success'))
    $(document).ready(function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: {!! json_encode(session('success')) !!},
            showConfirmButton: false,
            timer: 2000
        });
    });
    @endif

    $(document).ready(function() {
        // 1. Inisialisasi DataTables Server-Side
        var table = $('#tableArchive').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('product.trash') }}",
                data: function(d) {
                    d.category_id = $('#filter_category').val();
                    d.division_id = $('#filter_division').val();
                    d.held_by_id = $('#filter_held_by').val();
                    d.location_id = $('#filter_location').val();
                    d.condition = $('#filter_condition').val();
                }
            },
            columns: [
                { data: 'asset_name', name: 'name' },
                { data: 'status_badge', name: 'is_active' },
                { data: 'description', name: 'logs.description', orderable: false },
                { data: 'condition_badge', name: 'condition' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).find('td:eq(0)').attr('data-label', 'NAMA ASSET');
                $(row).find('td:eq(1)').attr('data-label', 'STATUS KELUAR');
                $(row).find('td:eq(2)').attr('data-label', 'KETERANGAN');
                $(row).find('td:eq(3)').attr('data-label', 'KONDISI');
                $(row).find('td:eq(4)').attr('data-label', 'AKSI');
            },
            language: {
                search: "",
                searchPlaceholder: "Cari arsip..."
            },
            order: [[0, 'desc']]
        });

        $('#filter_category, #filter_division, #filter_held_by, #filter_location, #filter_condition').change(function(){
            table.draw();
        });

        $('#btnReset').click(function() {
            $('#filter_category, #filter_division, #filter_held_by, #filter_location, #filter_condition').val('');
            table.draw();
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
                                <small class="text-muted d-block fw-bold mb-2">Kondisi</small>
                                <div class="d-flex justify-content-center">
                                    ${getConditionBadge(data.condition)}
                                </div>
                            </div>
                        </div>

                        <table class="table table-sm table-borderless" style="font-size: 0.95rem;">
                            <tr><td width="40%" class="text-muted">Supplier</td><td class="fw-bold">: ${data.supplier?.name || '-'}</td></tr>
                            <tr><td class="text-muted">Tanggal Beli</td><td>: ${tglBeli}</td></tr>
                            <tr><td class="text-muted">Harga Beli</td><td class="fw-bold text-success">: ${harga}</td></tr>
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