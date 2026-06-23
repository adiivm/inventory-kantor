@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 767px) {
        .table-responsive {
            overflow: visible !important;
        }
        #tableItems {
            width: 100% !important;
        }
        #tableItems thead {
            display: none;
        }
        #tableItems tbody tr {
            display: block;
            margin-bottom: 1rem;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 12px;
        }
        #tableItems tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }
        #tableItems tbody td:last-child {
            border-bottom: none;
        }
        #tableItems tbody td::before {
            content: attr(data-label);
            font-size: 0.75rem;
            font-weight: 600;
            color: #a3adc2;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            flex-shrink: 0;
            margin-right: 10px;
        }
        #tableItems tbody td[data-label="AKSI"]::before,
        #tableItems tbody td[data-label=""]::before {
            display: none;
        }
    }
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 2px;
        width: 100%;
    }
    .action-buttons .btn {
        padding: 0.1rem 0.3rem;
        font-size: 0.65rem;
        line-height: 1.1;
    }
    .action-buttons .btn i {
        font-size: 0.7rem;
    }
    .btn-plus-input {
        border-color: #dee2e6 !important;
        border-left: 0 !important;
        background: #fff;
        color: #6c757d;
    }
    .btn-plus-input:hover {
        background: #f8f9fa;
        color: #495057;
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
                            <i class="bi bi-boxes me-2"></i>Master Barang Consumable
                        </h2>
                        <p class="text-muted mb-0">Kelola barang habis pakai, stok, dan batas minimal</p>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('consumable.items.import_template') }}" class="btn btn-outline-success w-100 fw-bold px-md-3">
                            <i class="bi bi-download me-2"></i> <span class="d-none d-md-inline">Download Template</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-auto">
                        <button class="btn btn-outline-primary w-100 fw-bold px-md-3" data-bs-toggle="modal" data-bs-target="#modalImport">
                            <i class="bi bi-upload me-2"></i> <span class="d-none d-md-inline">Import Excel</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-auto ms-md-auto">
                        <button class="btn btn-primary w-100 fw-bold px-md-4" data-bs-toggle="modal" data-bs-target="#modalItem">
                            <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-md-inline">Tambah Barang</span>
                        </button>
                    </div>
                </div>

                {{-- Filter Row --}}
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-muted small mb-1">Kategori</label>
                        <select id="filter_category" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-muted small mb-1">Supplier</label>
                        <select id="filter_supplier" class="form-select form-select-sm">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold text-muted small mb-1">Status Stok</label>
                        <select id="filter_stock_status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="low">Stok Menipis</option>
                            <option value="out">Habis</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 d-flex align-items-end">
                        <button id="btnResetFilter" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tableItems" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th>Min. Stok</th>
                                    <th>Supplier</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah / Edit Barang --}}
<div class="modal fade" id="modalItem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title ps-2" id="modalItemTitle"><i class="bi bi-plus-circle me-2"></i>Tambah Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formItem">
                @csrf
                <input type="hidden" id="itemId" name="id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" id="itemName" name="name" class="form-control form-control-lg border-2" placeholder="Contoh: Ballpoint Pilot" style="border-radius: 10px;" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Kategori Consumable <span class="text-danger">*</span></label>
                        <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                            <select id="itemCategory" name="category_id" class="form-select form-select-lg border-2" style="border-right: 0; border-radius: 10px 0 0 10px;" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-plus-input" type="button" style="border: 2px solid #dee2e6; border-left: 0; border-radius: 0 10px 10px 0;" onclick="openQuickAdd('category')" title="Tambah Kategori Baru">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted">Stok Saat Ini <span class="text-danger">*</span></label>
                            <input type="number" id="itemStock" name="current_stock" class="form-control form-control-lg border-2" value="0" min="0" style="border-radius: 10px;" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted">Satuan <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                                <select id="itemUnit" name="unit" class="form-select form-select-lg border-2" style="border-right: 0; border-radius: 10px 0 0 10px;" required>
                                    <option value="">Pilih Satuan</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-plus-input" type="button" style="border: 2px solid #dee2e6; border-left: 0; border-radius: 0 10px 10px 0;" onclick="openQuickAdd('unit')" title="Tambah Satuan Baru">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted">Minimal Stok <span class="text-danger">*</span></label>
                            <input type="number" id="itemMinStock" name="min_stock" class="form-control form-control-lg border-2" value="0" min="0" style="border-radius: 10px;" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted">Supplier</label>
                            <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                                <select id="itemSupplier" name="supplier_id" class="form-select form-select-lg border-2" style="border-right: 0; border-radius: 10px 0 0 10px;">
                                    <option value="">Pilih Supplier</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-plus-input" type="button" style="border: 2px solid #dee2e6; border-left: 0; border-radius: 0 10px 10px 0;" onclick="openQuickAdd('supplier')" title="Tambah Supplier Baru">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSaveItem" style="border-radius: 10px;">
                        <i class="bi bi-floppy me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Quick Add Category --}}
<div class="modal fade" id="modalQuickCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-folder-plus me-2"></i>Tambah Kategori</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formQuickCategory">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" id="quickCategoryName" name="name" class="form-control form-control-lg border-2" style="border-radius: 10px;" placeholder="Contoh: ATK" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-muted">Deskripsi</label>
                        <textarea id="quickCategoryDesc" name="description" class="form-control border-2" rows="2" style="border-radius: 10px;" placeholder="Opsional"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 10px;">
                        <i class="bi bi-floppy me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Quick Add Unit --}}
<div class="modal fade" id="modalQuickUnit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Satuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formQuickUnit">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" id="quickUnitName" name="name" class="form-control form-control-lg border-2" style="border-radius: 10px;" placeholder="Contoh: pcs, pack" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 10px;">
                        <i class="bi bi-floppy me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Quick Add Supplier --}}
<div class="modal fade" id="modalQuickSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Tambah Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formQuickSupplier">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Supplier <span class="text-danger">*</span></label>
                        <input type="text" id="quickSupplierName" name="name" class="form-control form-control-lg border-2" style="border-radius: 10px;" placeholder="Nama supplier" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold text-muted">Kontak</label>
                        <input type="text" id="quickSupplierPhone" name="phone" class="form-control border-2" style="border-radius: 10px;" placeholder="No. telepon">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 10px;">
                        <i class="bi bi-floppy me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Import Excel --}}
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Import Excel Master Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formImport" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0" style="border-radius: 10px;">
                        <i class="bi bi-info-circle me-2"></i>
                        Upload file Excel (.xlsx, .xls, .csv) dengan kolom: <strong>name, category, unit, supplier, min_stock, current_stock</strong>.
                        Kategori akan dibuat otomatis jika belum ada.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Pilih File</label>
                        <input type="file" name="file" class="form-control border-2" accept=".xlsx,.xls,.csv" required style="border-radius: 10px;">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm" style="border-radius: 10px;">
                        <i class="bi bi-upload me-1"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        function getFilterParams() {
            return {
                category_id: $('#filter_category').val(),
                supplier_id: $('#filter_supplier').val(),
                stock_status: $('#filter_stock_status').val(),
            };
        }

        const table = $('#tableItems').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('consumable.items') }}",
                data: function(d) {
                    Object.assign(d, getFilterParams());
                },
            },
            columnDefs: [
                { width: '5%', targets: 0 },
                { width: '20%', targets: 1 },
                { width: '15%', targets: 2 },
                { width: '10%', targets: [3, 4, 5] },
                { width: '15%', targets: 6 },
                { width: '20%', targets: 7 },
            ],
            columns: [
                { data: 'sku', name: 'sku', defaultContent: '-' },
                { data: 'name', name: 'name' },
                { data: 'category.name', name: 'category.name', defaultContent: '-' },
                { data: 'current_stock', name: 'current_stock' },
                { data: 'unit', name: 'unit' },
                { data: 'min_stock', name: 'min_stock' },
                { data: 'supplier_name', name: 'supplier_name', defaultContent: '-' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            createdRow: function(row, data, dataIndex) {
                $(row).find('td:eq(0)').attr('data-label', 'SKU');
                $(row).find('td:eq(1)').attr('data-label', 'Nama Barang');
                $(row).find('td:eq(2)').attr('data-label', 'Kategori');
                $(row).find('td:eq(3)').attr('data-label', 'Stok');
                $(row).find('td:eq(4)').attr('data-label', 'Satuan');
                $(row).find('td:eq(5)').attr('data-label', 'Min. Stok');
                $(row).find('td:eq(6)').attr('data-label', 'Supplier');
                $(row).find('td:eq(7)').attr('data-label', 'Aksi');
                $(row).find('td:eq(7)').html('<div class="action-buttons">' + $(row).find('td:eq(7)').html() + '</div>');
            },
            language: {
                search: "",
                searchPlaceholder: "Cari barang...",
            },
            order: [[1, 'asc']],
        });

        // ── Filter change events ──
        $('#filter_category, #filter_supplier, #filter_stock_status').change(function() {
            table.ajax.reload();
        });

        $('#btnResetFilter').click(function() {
            $('#filter_category, #filter_supplier, #filter_stock_status').val('');
            table.ajax.reload();
        });

        // ── Handle URL query params (from dashboard card clicks) ──
        const urlParams = new URLSearchParams(window.location.search);
        const stockStatus = urlParams.get('stock_status');
        if (stockStatus) {
            $('#filter_stock_status').val(stockStatus);
            // Bersihkan query string dari URL tanpa reload halaman
            if (window.history.replaceState) {
                const newUrl = window.location.pathname;
                window.history.replaceState({}, '', newUrl);
            }
            table.ajax.reload();
        }

        // --- Submit form barang ---
        $('#formItem').on('submit', function(e) {
            e.preventDefault();
            const id = $('#itemId').val();
            const isEdit = !!id;
            const url = isEdit ? `/consumable/items/${id}` : '/consumable/items';

            const data = {
                _token: csrfToken,
                name: $('#itemName').val(),
                category_id: $('#itemCategory').val(),
                current_stock: $('#itemStock').val(),
                unit: $('#itemUnit').val(),
                min_stock: $('#itemMinStock').val(),
                supplier_id: $('#itemSupplier').val() || '',
            };

            $.ajax({
                url: url,
                method: isEdit ? 'PUT' : 'POST',
                data: isEdit ? { ...data, _method: 'PUT' } : data,
                success: function(res) {
                    $('#modalItem').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                    table.ajax.reload();
                    resetForm();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                }
            });
        });

        // --- Edit barang ---
        $('#tableItems').on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            $.get(`/consumable/items/${id}/edit`, function(data) {
                $('#modalItemTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Barang');
                $('#itemId').val(data.id);
                $('#itemName').val(data.name);
                $('#itemCategory').val(data.category_id);
                $('#itemStock').val(data.current_stock);
                $('#itemUnit').val(data.unit);
                $('#itemMinStock').val(data.min_stock);
                $('#itemSupplier').val(data.supplier_id || '');
                $('#modalItem').modal('show');
            });
        });

        // --- Hapus barang ---
        $('#tableItems').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Yakin hapus?',
                text: 'Barang akan dipindahkan ke trash.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dd4b39',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/consumable/items/${id}`,
                        method: 'DELETE',
                        data: { _token: csrfToken, _method: 'DELETE' },
                        success: function(res) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Gagal menghapus';
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                        }
                    });
                }
            });
        });

        $('#modalItem').on('hidden.bs.modal', function() {
            resetForm();
        });

        function resetForm() {
            $('#formItem')[0].reset();
            $('#itemId').val('');
            $('#modalItemTitle').html('<i class="bi bi-plus-circle me-2"></i>Tambah Barang');
        }

        // --- Quick Add Category ---
        $('#formQuickCategory').on('submit', function(e) {
            e.preventDefault();
            const name = $('#quickCategoryName').val();
            const desc = $('#quickCategoryDesc').val();
            $.ajax({
                url: '/consumable/categories',
                method: 'POST',
                data: { _token: csrfToken, name: name, description: desc },
                success: function(res) {
                    $('#modalQuickCategory').modal('hide');
                    $('#quickCategoryName').val('');
                    $('#quickCategoryDesc').val('');
                    // Refresh category dropdown & select new
                    refreshCategories(res.data.id);
                    Swal.fire({ icon: 'success', title: 'Kategori ditambahkan', timer: 1500, showConfirmButton: false });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Error' });
                }
            });
        });

        // --- Quick Add Unit ---
        $('#formQuickUnit').on('submit', function(e) {
            e.preventDefault();
            const name = $('#quickUnitName').val();
            $.ajax({
                url: '/consumable/units',
                method: 'POST',
                data: { _token: csrfToken, name: name },
                success: function(res) {
                    $('#modalQuickUnit').modal('hide');
                    $('#quickUnitName').val('');
                    refreshUnits(res.data.name);
                    Swal.fire({ icon: 'success', title: 'Satuan ditambahkan', timer: 1500, showConfirmButton: false });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Error' });
                }
            });
        });

        // --- Quick Add Supplier ---
        $('#formQuickSupplier').on('submit', function(e) {
            e.preventDefault();
            const name = $('#quickSupplierName').val();
            const phone = $('#quickSupplierPhone').val();
            $.ajax({
                url: '/api/suppliers',
                method: 'POST',
                data: { _token: csrfToken, name: name, phone: phone },
                success: function(res) {
                    $('#modalQuickSupplier').modal('hide');
                    $('#quickSupplierName').val('');
                    $('#quickSupplierPhone').val('');
                    refreshSuppliers(res.data.id);
                    Swal.fire({ icon: 'success', title: 'Supplier ditambahkan', timer: 1500, showConfirmButton: false });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Error' });
                }
            });
        });

        function refreshCategories(selectedId) {
            $.get('/api/consumable-categories', function(data) {
                const $sel = $('#itemCategory');
                $sel.find('option:not([value=""])').remove();
                data.forEach(function(item) {
                    $sel.append(`<option value="${item.id}">${item.name}</option>`);
                });
                $sel.val(selectedId);
            });
        }

        function refreshUnits(selectedName) {
            $.get('/api/consumable-units', function(data) {
                const $sel = $('#itemUnit');
                $sel.find('option:not([value=""])').remove();
                data.forEach(function(item) {
                    $sel.append(`<option value="${item.name}">${item.name}</option>`);
                });
                $sel.val(selectedName);
            });
        }

        function refreshSuppliers(selectedId) {
            $.get('/api/suppliers', function(data) {
                const $sel = $('#itemSupplier');
                $sel.find('option:not([value=""])').remove();
                data.forEach(function(item) {
                    $sel.append(`<option value="${item.id}">${item.name}</option>`);
                });
                $sel.val(selectedId);
            });
        }

        // --- Import ---
        $('#formImport').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: '/consumable/items/import',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': csrfToken },
                beforeSend: function() {
                    $('#formImport').find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Meng-import...');
                },
                success: function(res) {
                    $('#modalImport').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                    table.ajax.reload();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal import';
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                },
                complete: function() {
                    $('#formImport').find('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Import Data');
                    $('#formImport')[0].reset();
                }
            });
        });
    });

    // Global function accessible from inline onclick
    function openQuickAdd(type) {
        if (type === 'category') {
            $('#modalQuickCategory').modal('show');
        } else if (type === 'unit') {
            $('#modalQuickUnit').modal('show');
        } else if (type === 'supplier') {
            $('#modalQuickSupplier').modal('show');
        }
    }
</script>
@endpush
