@extends('layouts.app') 

@section('content')
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
                    <div class="col-12 col-md-auto ms-md-auto">
                        <a href="/product/create" class="btn btn-primary w-100 fw-bold px-md-5">
                            + Tambah Barang
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tableProduct" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">SKU</th>
                                <th width="25%">NAMA ASSETS</th>
                                <th width="10%">IMAGE</th>
                                <th width="10%">KATEGORI</th>
                                <th width="10%">DIVISI</th>
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

@endsection

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Mantap!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2000
        });
    </script>
@endif

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Inisialisasi DataTable
        var table = $('#tableProduct').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('product.index') }}",
            columns: [
                { data: 'sku_custom', name: 'sku' },
                { data: 'asset_info', name: 'name' },
                { data: 'image_thumb', name: 'id', orderable: false },
                { data: 'category.name', name: 'category.name', defaultContent: '-' },
                { data: 'division.name', name: 'division.name', defaultContent: '-' },
                
                // --- PASTIKAN BARIS INI MENGGUNAKAN condition_badge ---
                { data: 'condition_badge', name: 'condition' }, 
                
                { data: 'holder_info', name: 'heldBy.name', defaultContent: '-' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            // Styling tambahan agar mirip gambar
            language: {
                search: "",
                searchPlaceholder: "Cari barang..."
            }
        });

        // 2. Fungsi Filter (Tetap Aktif)
        $('#filter_category, #filter_division, #filter_held_by, #filter_condition').change(function(){
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
                                <small class="text-muted d-block fw-bold mb-2">Rincian Stok (Total: ${data.stock})</small>
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