@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">Supplier Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" onclick="resetSupplierForm()" data-bs-toggle="modal" data-bs-target="#modalSupplier">
            <i class="bi bi-plus-lg"></i> Tambah Supplier
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="supplierTable">
                <thead>
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
        ]
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
                console.log('Response status:', xhr.status);
                console.log('Response text:', xhr.responseText.substring(0, 500));
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