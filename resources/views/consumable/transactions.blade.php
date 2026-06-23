@extends('layouts.app')

@push('styles')
<style>
    @media (max-width: 767px) {
        #tableTransactions {
            width: 100% !important;
        }
        #tableTransactions thead { display: none; }
        #tableTransactions tbody tr {
            display: block;
            margin-bottom: 1rem;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 12px;
        }
        #tableTransactions tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }
        #tableTransactions tbody td:last-child { border-bottom: none; }
        #tableTransactions tbody td::before {
            content: attr(data-label);
            font-size: 0.75rem;
            font-weight: 600;
            color: #a3adc2;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            flex-shrink: 0;
            margin-right: 10px;
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
                            <i class="bi bi-arrow-left-right me-2"></i>Transaksi Stok Consumable
                        </h2>
                        <p class="text-muted mb-0">Catat barang masuk atau penyesuaian stok</p>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-auto ms-md-auto">
                        <button class="btn btn-primary w-100 fw-bold px-md-4" data-bs-toggle="modal" data-bs-target="#modalTransaction">
                            <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-md-inline">Tambah Transaksi</span>
                        </button>
                    </div>
                </div>

                {{-- Filter Row --}}
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small mb-1">Tipe</label>
                        <select id="filter_type" class="form-select form-select-sm">
                            <option value="">Semua Tipe</option>
                            <option value="in">Barang Masuk</option>
                            <option value="out">Barang Keluar</option>
                            <option value="adjustment">Penyesuaian</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small mb-1">Status</label>
                        <select id="filter_status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small mb-1">Barang</label>
                        <select id="filter_item" class="form-select form-select-sm">
                            <option value="">Semua Barang</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small mb-1">Dari Tanggal</label>
                        <input type="date" id="filter_date_start" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small mb-1">Sampai Tanggal</label>
                        <input type="date" id="filter_date_end" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-2 d-flex align-items-end">
                        <button id="btnResetFilter" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tableTransactions" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">Tanggal</th>
                                <th width="20%">Barang</th>
                                <th width="10%">Tipe</th>
                                <th width="8%">Qty</th>
                                <th width="10%">Ref Number</th>
                                <th width="12%">Keterangan</th>
                                <th width="10%">Status</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Transaksi --}}
<div class="modal fade" id="modalTransaction" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Transaksi Stok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTransaction">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Barang <span class="text-danger">*</span></label>
                        <select name="consumable_item_id" class="form-select form-select-lg border-2" style="border-radius: 10px;" required>
                            <option value="">Pilih Barang</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} (stok: {{ $item->current_stock }} {{ $item->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted">Tipe <span class="text-danger">*</span></label>
                            <select name="type" class="form-select form-select-lg border-2" style="border-radius: 10px;" required>
                                <option value="in">Barang Masuk</option>
                                <option value="adjustment">Penyesuaian</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted">Qty <span class="text-danger">*</span></label>
                            <input type="number" name="qty" class="form-control form-control-lg border-2" value="1" style="border-radius: 10px;" required>
                            <small class="text-muted">Gunakan nilai negatif (-) untuk mengurangi stok (khusus Penyesuaian)</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control form-control-lg border-2" value="{{ date('Y-m-d') }}" style="border-radius: 10px;" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-muted">Ref Number</label>
                            <input type="text" name="reference_number" class="form-control form-control-lg border-2" style="border-radius: 10px;" placeholder="Opsional">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Keterangan</label>
                        <textarea name="notes" class="form-control border-2" rows="2" style="border-radius: 10px;" placeholder="Opsional"></textarea>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        function getFilterParams() {
            return {
                type: $('#filter_type').val(),
                status: $('#filter_status').val(),
                consumable_item_id: $('#filter_item').val(),
                date_start: $('#filter_date_start').val(),
                date_end: $('#filter_date_end').val(),
            };
        }

        const table = $('#tableTransactions').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('consumable.transactions') }}",
                data: function(d) {
                    Object.assign(d, getFilterParams());
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'date', name: 'date' },
                { data: 'consumable_item.name', name: 'consumable_item.name', defaultContent: '-' },
                { data: 'type', name: 'type' },
                { data: 'qty', name: 'qty' },
                { data: 'reference_number', name: 'reference_number', defaultContent: '-' },
                { data: 'notes', name: 'notes', defaultContent: '-' },
                { data: 'status_badge', name: 'status', orderable: true, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            createdRow: function(row, data, dataIndex) {
                const labels = ['No','Tanggal','Barang','Tipe','Qty','Ref Number','Keterangan','Status','Aksi'];
                $(row).find('td').each(function(i) {
                    $(this).attr('data-label', labels[i] || '');
                });
            },
            language: {
                search: "",
                searchPlaceholder: "Cari transaksi...",
            },
            order: [[1, 'desc']],
        });

        // ── Filter change events ──
        $('#filter_type, #filter_status, #filter_item, #filter_date_start, #filter_date_end').change(function() {
            table.ajax.reload();
        });

        $('#btnResetFilter').click(function() {
            $('#filter_type, #filter_status, #filter_item').val('');
            $('#filter_date_start, #filter_date_end').val('');
            table.ajax.reload();
        });

        // ── Handle URL query params ──
        (function() {
            const params = new URLSearchParams(window.location.search);
            let needsReload = false;

            const status = params.get('status');
            if (status) {
                $('#filter_status').val(status);
                needsReload = true;
            }

            const itemId = params.get('consumable_item_id');
            if (itemId) {
                $('#filter_item').val(itemId);
                $('select[name="consumable_item_id"]').val(itemId);
                needsReload = true;
            }

            const type = params.get('type');
            if (type) {
                $('#filter_type').val(type);
                $('select[name="type"]').val(type);
                needsReload = true;
            }

            if (window.history.replaceState) {
                window.history.replaceState({}, '', window.location.pathname);
            }

            if (needsReload) {
                table.ajax.reload();
            }

            if (itemId) {
                $('#modalTransaction').modal('show');
            }
        })();

        $('#formTransaction').on('submit', function(e) {
            e.preventDefault();
            const data = $(this).serialize();

            $.ajax({
                url: '/consumable/transactions',
                method: 'POST',
                data: data,
                success: function(res) {
                    $('#modalTransaction').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                    table.ajax.reload();
                    $('#formTransaction')[0].reset();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal menyimpan transaksi';
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                }
            });
        });

        $('#modalTransaction').on('hidden.bs.modal', function() {
            $('#formTransaction')[0].reset();
        });

        // ── Approve Stock In ──
        $('#tableTransactions').on('click', '.btn-approve-stock', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Setujui transaksi?',
                text: 'Stok barang akan bertambah jika disetujui.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/consumable/transactions/${id}/approve`,
                        method: 'POST',
                        data: { _token: csrfToken },
                        success: function(res) {
                            Swal.fire({ icon: 'success', title: 'Disetujui!', text: res.message, timer: 2000, showConfirmButton: false });
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Gagal approve';
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                        }
                    });
                }
            });
        });

        // ── Reject Stock In ──
        $('#tableTransactions').on('click', '.btn-reject-stock', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Tolak transaksi?',
                text: 'Transaksi ini akan ditolak dan stok tidak akan bertambah.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/consumable/transactions/${id}/reject`,
                        method: 'POST',
                        data: { _token: csrfToken },
                        success: function(res) {
                            Swal.fire({ icon: 'success', title: 'Ditolak!', text: res.message, timer: 2000, showConfirmButton: false });
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Gagal reject';
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
