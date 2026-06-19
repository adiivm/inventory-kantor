@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4 border-0 shadow-sm">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="fw-bold text-dark">
                            <i class="bi bi-rulers me-2"></i>Master Satuan Consumable
                        </h2>
                        <p class="text-muted mb-0">Kelola satuan barang habis pakai</p>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-auto ms-md-auto">
                        <button class="btn btn-primary w-100 fw-bold px-md-4" data-bs-toggle="modal" data-bs-target="#modalUnit">
                            <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-md-inline">Tambah Satuan</span>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tableUnits" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">No</th>
                                <th width="60%">Nama Satuan</th>
                                <th width="30%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah / Edit Satuan --}}
<div class="modal fade" id="modalUnit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title ps-2" id="modalUnitTitle"><i class="bi bi-plus-circle me-2"></i>Tambah Satuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUnit">
                @csrf
                <input type="hidden" id="unitId" name="id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Satuan <span class="text-danger">*</span></label>
                        <input type="text" id="unitName" name="name" class="form-control form-control-lg border-2" placeholder="Contoh: pcs, pack, botol" style="border-radius: 10px;" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSaveUnit" style="border-radius: 10px;">
                        <i class="bi bi-floppy me-1"></i> Simpan
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

        const table = $('#tableUnits').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('consumable.units') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            language: {
                search: "",
                searchPlaceholder: "Cari satuan...",
            },
            order: [[1, 'asc']],
        });

        $('#formUnit').on('submit', function(e) {
            e.preventDefault();
            const id = $('#unitId').val();
            const isEdit = !!id;
            const url = isEdit ? `/consumable/units/${id}` : '/consumable/units';
            const data = {
                _token: csrfToken,
                name: $('#unitName').val(),
            };

            $.ajax({
                url: url,
                method: isEdit ? 'PUT' : 'POST',
                data: isEdit ? { ...data, _method: 'PUT' } : data,
                success: function(res) {
                    $('#modalUnit').modal('hide');
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

        $('#tableUnits').on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            $.get(`/consumable/units/${id}/edit`, function(data) {
                $('#modalUnitTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Satuan');
                $('#unitId').val(data.id);
                $('#unitName').val(data.name);
                $('#modalUnit').modal('show');
            });
        });

        $('#tableUnits').on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Yakin hapus?',
                text: 'Satuan akan dihapus permanent.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dd4b39',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/consumable/units/${id}`,
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

        $('#modalUnit').on('hidden.bs.modal', function() {
            resetForm();
        });

        function resetForm() {
            $('#formUnit')[0].reset();
            $('#unitId').val('');
            $('#modalUnitTitle').html('<i class="bi bi-plus-circle me-2"></i>Tambah Satuan');
        }
    });
</script>
@endpush
