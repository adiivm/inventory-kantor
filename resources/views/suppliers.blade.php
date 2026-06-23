@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 767px) {
        .table-responsive {
            overflow: visible !important;
        }
        #supplierTable {
            width: 100% !important;
        }
        #supplierTable thead {
            display: none;
        }
        #supplierTable tbody tr {
            display: block;
            margin-bottom: 1rem;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 12px;
        }
        #supplierTable tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }
        #supplierTable tbody td:last-child {
            border-bottom: none;
        }
        #supplierTable tbody td::before {
            content: attr(data-label);
            font-size: 0.75rem;
            font-weight: 600;
            color: #a3adc2;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            flex-shrink: 0;
            margin-right: 10px;
        }
        #supplierTable tbody td[data-label="AKSI"]::before,
        #supplierTable tbody td[data-label=""]::before {
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
                            <i class="bi bi-person-lines-fill me-2"></i>Supplier Management
                        </h2>
                        <p class="text-muted mb-0">Kelola data pemasok barang</p>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('suppliers.import_template') }}" class="btn btn-outline-success w-100 fw-bold px-md-3">
                            <i class="bi bi-download me-2"></i> <span class="d-none d-md-inline">Download Template</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-auto">
                        <button class="btn btn-outline-primary w-100 fw-bold px-md-3" data-bs-toggle="modal" data-bs-target="#modalImport">
                            <i class="bi bi-upload me-2"></i> <span class="d-none d-md-inline">Import Excel</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-auto ms-md-auto">
                        <button class="btn btn-primary w-100 fw-bold px-md-4" onclick="resetSupplierForm()" data-bs-toggle="modal" data-bs-target="#modalSupplier">
                            <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-md-inline">Tambah Supplier</span>
                        </button>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="supplierTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Supplier</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Alamat</th>
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

<!-- Modal Tambah/Edit Supplier -->
<div class="modal fade" id="modalSupplier" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSupplier" method="POST">
                @csrf
                <input type="hidden" name="_method" id="method_field" value="POST">
                <input type="hidden" id="supplier_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier *</label>
                        <input type="text" class="form-control" id="supplier_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="supplier_contact" name="contact_person">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="supplier_phone" name="phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="supplier_email" name="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" id="supplier_address" name="address" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" id="supplier_notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Import --}}
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Import Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formImport" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Upload file Excel (.xlsx, .xls, .csv) dengan format sesuai template.</p>
                    <div class="mb-3">
                        <label class="form-label">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#supplierTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: '{{ route("suppliers.index") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex' },
            { data: 'name', name: 'name' },
            { data: 'contact_person', name: 'contact_person' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'address', name: 'address' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).find('td:eq(0)').attr('data-label', 'No');
            $(row).find('td:eq(1)').attr('data-label', 'Nama Supplier');
            $(row).find('td:eq(2)').attr('data-label', 'Contact Person');
            $(row).find('td:eq(3)').attr('data-label', 'Phone');
            $(row).find('td:eq(4)').attr('data-label', 'Email');
            $(row).find('td:eq(5)').attr('data-label', 'Alamat');
            $(row).find('td:eq(6)').attr('data-label', 'Aksi');
            $(row).find('td:eq(6)').html('<div class="action-buttons">' + $(row).find('td:eq(6)').html() + '</div>');
        }
    });

    $('#formSupplier').on('submit', function(e) {
        e.preventDefault();
        const id = $('#supplier_id').val();
        const url = id ? `/suppliers/${id}` : '/suppliers';
        const method = id ? 'PUT' : 'POST';
        $('#method_field').val(method);

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function() {
                $('#modalSupplier').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data supplier berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan saat menyimpan data.';
                if (xhr.responseJSON?.errors) {
                    const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                    msg = xhr.responseJSON.errors[firstKey][0];
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire('Gagal', msg, 'error');
            }
        });
    });

    $('#formImport').on('submit', function(e) {
        e.preventDefault();
        var form = new FormData(this);
        $.ajax({
            url: '{{ route("suppliers.import") }}',
            type: 'POST',
            data: form,
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                $('#modalImport').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false })
                    .then(() => window.location.reload());
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'Import gagal.';
                Swal.fire('Gagal', msg, 'error');
            }
        });
    });
});

function resetSupplierForm() {
    $('#formSupplier')[0].reset();
    $('#supplier_id').val('');
    $('#method_field').val('POST');
    $('.modal-title').text('Tambah Supplier');
}

function editSupplier(id) {
    $.get(`/suppliers/${id}/edit`, function(data) {
        $('#formSupplier')[0].reset();
        $('#supplier_id').val(data.id);
        $('#supplier_name').val(data.name);
        $('#supplier_contact').val(data.contact_person || '');
        $('#supplier_phone').val(data.phone || '');
        $('#supplier_email').val(data.email || '');
        $('#supplier_address').val(data.address || '');
        $('#supplier_notes').val(data.notes || '');
        $('#method_field').val('PUT');
        $('.modal-title').text('Edit Supplier: ' + data.name);
        $('#modalSupplier').modal('show');
    });
}

function deleteSupplier(id) {
    Swal.fire({
        title: 'Hapus Supplier?',
        text: 'Data yang dihapus tidak bisa dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/suppliers/${id}`,
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Supplier berhasil dihapus.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Tidak bisa menghapus supplier.', 'error');
                }
            });
        }
    });
}
</script>
@endpush