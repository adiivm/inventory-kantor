@extends('layouts.app')

@push('styles')
<style>
    #tableDistributions thead th { white-space: nowrap; }
    .dyn-row { background: #f8f9fa; border-radius: 8px; padding: 10px; margin-bottom: 8px; }
    .dyn-row .btn-remove-row { align-self: center; }
    .sig-canvas {
        border: 2px dashed #adb5bd;
        border-radius: 10px;
        width: 100%;
        height: 150px;
        cursor: crosshair;
        background: #fff;
        touch-action: none;
    }
    .sig-canvas.active { border-color: #0d6efd; background: #f8fbff; }
    .sig-label { font-size: 0.85rem; font-weight: 600; color: #6c757d; }
    @media (max-width: 767px) {
        #tableDistributions {
            width: 100% !important;
        }
        #tableDistributions thead { display: none; }
        #tableDistributions tbody tr {
            display: block;
            margin-bottom: 1rem;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 12px;
        }
        #tableDistributions tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }
        #tableDistributions tbody td:last-child { border-bottom: none; }
        #tableDistributions tbody td::before {
            content: attr(data-label);
            font-size: 0.75rem;
            font-weight: 600;
            color: #a3adc2;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            flex-shrink: 0;
            margin-right: 10px;
        }
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 4px;
            width: 100%;
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
                            <i class="bi bi-truck me-2"></i>Distribusi Consumable
                        </h2>
                        <p class="text-muted mb-0">Kelola permintaan barang habis pakai</p>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-auto ms-md-auto">
                        <button class="btn btn-primary w-100 fw-bold px-md-4" data-bs-toggle="modal" data-bs-target="#modalCreateDistribution">
                            <i class="bi bi-plus-lg me-1"></i> <span class="d-none d-md-inline">Buat Permintaan</span>
                        </button>
                    </div>
                </div>

                {{-- Filter Row --}}
                <div class="row g-2 mb-3 align-items-end">
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
                        <label class="form-label fw-semibold text-muted small mb-1">Divisi</label>
                        <select id="filter_division" class="form-select form-select-sm">
                            <option value="">Semua Divisi</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
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
                    <table class="table table-hover align-middle" id="tableDistributions" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Ref Number</th>
                                <th width="15%">Peminta</th>
                                <th width="12%">Divisi</th>
                                <th width="12%">Tanggal</th>
                                <th width="10%">Status</th>
                                <th width="18%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
        </div>
    </div>
</div>

{{-- Modal Quick Add Divisi --}}
<div class="modal fade" id="modalQuickDivision" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-building-plus me-2"></i>Tambah Divisi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formQuickDivision">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Divisi <span class="text-danger">*</span></label>
                        <input type="text" id="quickDivisionName" name="name" class="form-control form-control-lg border-2" style="border-radius: 10px;" placeholder="Nama divisi" required>
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
    </div>
</div>

{{-- Modal Buat Permintaan --}}
<div class="modal fade" id="modalCreateDistribution" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Buat Permintaan Distribusi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formDistribution">
                @csrf
                <div class="modal-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold text-muted">Nama Peminta <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                                <select id="distRequester" name="requester_name" class="form-select form-select-lg border-2" style="border-right: 0; border-radius: 10px 0 0 10px;" required>
                                    <option value="">Pilih Peminta</option>
                                    @foreach($heldBies as $hb)
                                        <option value="{{ $hb->name }}">{{ $hb->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-plus-input" type="button" id="btnAddHeldBy" style="border: 2px solid #dee2e6; border-left: 0; border-radius: 0 10px 10px 0;" title="Tambah Peminta Baru">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted">Divisi <span class="text-danger">*</span></label>
                            <div class="input-group" style="border-radius: 10px; overflow: hidden;">
                                <select id="distDivision" name="division_id" class="form-select form-select-lg border-2" style="border-right: 0; border-radius: 10px 0 0 10px;" required>
                                    <option value="">Pilih Divisi</option>
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-plus-input" type="button" id="btnAddDivision" style="border: 2px solid #dee2e6; border-left: 0; border-radius: 0 10px 10px 0;" title="Tambah Divisi Baru">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-muted"><i class="bi bi-box-seam me-1"></i>Barang yang Diminta</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm fw-bold" id="btnAddRow">
                            <i class="bi bi-plus-lg"></i> Tambah Barang
                        </button>
                    </div>
                    <div id="itemsContainer"></div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 10px;">
                        <i class="bi bi-send me-1"></i> Kirim Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Signature --}}
<div class="modal fade" id="modalSignature" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Serah Terima & Tanda Tangan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSignature">
                @csrf
                <input type="hidden" id="sigDistributionId" value="">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="sig-label mb-2"><i class="bi bi-person-badge me-1"></i>Admin / Purchasing</div>
                            <canvas id="sigAdmin" class="sig-canvas"></canvas>
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearCanvas('sigAdmin')"><i class="bi bi-eraser"></i> Hapus</button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="sig-label mb-2"><i class="bi bi-person me-1"></i>Penerima</div>
                            <canvas id="sigReceiver" class="sig-canvas"></canvas>
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearCanvas('sigReceiver')"><i class="bi bi-eraser"></i> Hapus</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="sigAdminData" name="admin_signature">
                    <input type="hidden" id="sigReceiverData" name="receiver_signature">
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 10px;">
                        <i class="bi bi-save me-1"></i> Simpan Tanda Tangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Quick Add Peminta --}}
<div class="modal fade" id="modalQuickHeldBy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Tambah Peminta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formQuickHeldBy">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Peminta <span class="text-danger">*</span></label>
                        <input type="text" id="quickHeldByName" name="name" class="form-control form-control-lg border-2" style="border-radius: 10px;" placeholder="Nama lengkap" required>
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

{{-- Modal Detail --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-info text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Detail Permintaan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-6"><strong>Ref Number:</strong> <span id="detailRef"></span></div>
                    <div class="col-6"><strong>Peminta:</strong> <span id="detailRequester"></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Divisi:</strong> <span id="detailDivision"></span></div>
                    <div class="col-6"><strong>Tanggal:</strong> <span id="detailDate"></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Status:</strong> <span id="detailStatus"></span></div>
                </div>
                <hr>
                <h6 class="fw-bold text-muted">Barang</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Barang</th>
                                <th class="text-center" style="width:80px">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="detailItems"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    $(document).ready(function() {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        let rowIndex = 0;
        let sigAdminPad = null, sigReceiverPad = null;

        // --- DataTable ---
        function getFilterParams() {
            return {
                status: $('#filter_status').val(),
                division_id: $('#filter_division').val(),
                date_start: $('#filter_date_start').val(),
                date_end: $('#filter_date_end').val(),
            };
        }

        const table = $('#tableDistributions').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('consumable.distributions') }}",
                data: function(d) {
                    Object.assign(d, getFilterParams());
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'reference_number', name: 'reference_number' },
                { data: 'requester_name', name: 'requester_name' },
                { data: 'division.name', name: 'division.name', defaultContent: '-' },
                { data: 'created_at', name: 'created_at',
                    render: function(data) {
                        if (!data) return '-';
                        const d = new Date(data);
                        return isNaN(d) ? data.split('T')[0] : d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
                    }
                },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            createdRow: function(row, data, dataIndex) {
                const labels = ['No','Ref Number','Peminta','Divisi','Tanggal','Status','Aksi'];
                $(row).find('td').each(function(i) {
                    $(this).attr('data-label', labels[i] || '');
                });
                $(row).find('td:last').html('<div class="action-buttons">' + $(row).find('td:last').html() + '</div>');
            },
            language: {
                search: "",
                searchPlaceholder: "Cari distribusi...",
            },
            order: [[1, 'desc']],
        });

        // ── Filter change events ──
        $('#filter_status, #filter_division, #filter_date_start, #filter_date_end').change(function() {
            table.ajax.reload();
        });

        $('#btnResetFilter').click(function() {
            $('#filter_status, #filter_division').val('');
            $('#filter_date_start, #filter_date_end').val('');
            table.ajax.reload();
        });

        // ── Handle URL query params ──
        (function() {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');
            if (status) {
                $('#filter_status').val(status);
                if (window.history.replaceState) {
                    window.history.replaceState({}, '', window.location.pathname);
                }
                table.ajax.reload();
            }
        })();

        // --- Dynamic Rows ---
        function addRow(data) {
            const i = rowIndex++;
            const html = `
                <div class="dyn-row d-flex align-items-center gap-2" data-index="${i}">
                    <div style="flex:1;">
                        <select name="items[${i}][consumable_item_id]" class="form-select border-2" style="border-radius: 8px;" required>
                            <option value="">Pilih Barang</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" ${data && data.id == {{ $item->id }} ? 'selected' : ''}>
                                    {{ $item->name }} ({{ $item->current_stock }} {{ $item->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="width:120px; flex-shrink:0;">
                        <input type="number" name="items[${i}][qty]" class="form-control border-2" style="border-radius: 8px;" placeholder="Qty" min="1" value="${data ? data.qty : ''}" required>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="border-radius: 8px;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `;
            $('#itemsContainer').append(html);
        }

        $('#btnAddRow').on('click', function() { addRow(null); });

        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('.dyn-row').remove();
        });

        // --- Submit form ---
        $('#formDistribution').on('submit', function(e) {
            e.preventDefault();
            const data = $(this).serializeArray();

            // Quick validation: at least one item
            const items = data.filter(d => d.name.match(/items\[\d+\]\[consumable_item_id\]/));
            if (items.length === 0 || items.every(d => !d.value)) {
                Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Tambahkan minimal 1 barang.' });
                return;
            }

            $.ajax({
                url: '/consumable/distributions',
                method: 'POST',
                data: data,
                success: function(res) {
                    $('#modalCreateDistribution').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                    table.ajax.reload();
                    $('#formDistribution')[0].reset();
                    $('#itemsContainer').empty();
                    rowIndex = 0;
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Gagal membuat permintaan';
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                }
            });
        });

        $('#modalCreateDistribution').on('hidden.bs.modal', function() {
            $('#formDistribution')[0].reset();
            $('#itemsContainer').empty();
            rowIndex = 0;
        });

        // --- Detail ---
        window.detailDistribution = function(id) {
            $.get('/consumable/distributions/' + id, function(data) {
                $('#detailRef').text(data.reference_number);
                $('#detailRequester').text(data.requester_name);
                $('#detailDivision').text(data.division?.name || '-');
                const d = data.created_at ? new Date(data.created_at) : null;
                $('#detailDate').text(d && !isNaN(d) ? d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }) : '-');

                const statusMap = { pending: 'Pending', approved: 'Disetujui', rejected: 'Ditolak' };
                const badges = { pending: 'bg-warning text-dark', approved: 'bg-success', rejected: 'bg-danger' };
                $('#detailStatus').html('<span class="badge ' + (badges[data.status] || 'bg-secondary') + '">' + (statusMap[data.status] || data.status) + '</span>');

                let rows = '';
                if (data.details && data.details.length) {
                    data.details.forEach(function(d) {
                        rows += '<tr><td>' + (d.consumable_item?.name || 'Unknown') + '</td><td class="text-center">' + d.qty + '</td></tr>';
                    });
                } else {
                    rows = '<tr><td colspan="2" class="text-muted text-center">Tidak ada barang</td></tr>';
                }
                $('#detailItems').html(rows);
                $('#modalDetail').modal('show');
            });
        };

        // --- Approve ---
        window.approveDistribution = function(id) {
            Swal.fire({
                title: 'Setujui permintaan ini?',
                text: 'Stok barang akan dikurangi secara otomatis.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/consumable/distributions/' + id + '/approve',
                        method: 'POST',
                        data: { _token: csrfToken },
                        success: function(res) {
                            Swal.fire({ icon: 'success', title: 'Disetujui!', text: res.message, timer: 2000, showConfirmButton: false });
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Gagal approve';
                            const errors = xhr.responseJSON?.errors;
                            let text = msg;
                            if (errors && errors.length) text += '<br>' + errors.join('<br>');
                            Swal.fire({ icon: 'error', title: 'Gagal!', html: text });
                        }
                    });
                }
            });
        };

        // --- Reject ---
        window.rejectDistribution = function(id) {
            Swal.fire({
                title: 'Tolak permintaan ini?',
                text: 'Permintaan akan ditolak dan tidak dapat diproses.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Tolak',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/consumable/distributions/' + id + '/reject',
                        method: 'POST',
                        data: { _token: csrfToken },
                        success: function(res) {
                            Swal.fire({ icon: 'success', title: 'Ditolak!', text: res.message, timer: 2000, showConfirmButton: false });
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Gagal menolak' });
                        }
                    });
                }
            });
        };

        // --- Sign ---
        window.signDistribution = function(id) {
            $('#sigDistributionId').val(id);
            $('#modalSignature').modal('show');
        };

        // --- Signature Pad init ---
        function initSignaturePads() {
            const adminCanvas = document.getElementById('sigAdmin');
            const receiverCanvas = document.getElementById('sigReceiver');
            if (adminCanvas && !sigAdminPad) {
                sigAdminPad = new SignaturePad(adminCanvas);
                resizeCanvas(adminCanvas);
            }
            if (receiverCanvas && !sigReceiverPad) {
                sigReceiverPad = new SignaturePad(receiverCanvas);
                resizeCanvas(receiverCanvas);
            }
        }

        function resizeCanvas(canvas) {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * (window.devicePixelRatio || 1);
            canvas.height = rect.height * (window.devicePixelRatio || 1);
            const ctx = canvas.getContext('2d');
            ctx.scale(window.devicePixelRatio || 1, window.devicePixelRatio || 1);
        }

        window.clearCanvas = function(id) {
            const pad = id === 'sigAdmin' ? sigAdminPad : sigReceiverPad;
            if (pad) pad.clear();
        };

        $('#modalSignature').on('shown.bs.modal', function() {
            initSignaturePads();
        });

        $('#modalSignature').on('hidden.bs.modal', function() {
            if (sigAdminPad) { sigAdminPad.clear(); sigAdminPad = null; }
            if (sigReceiverPad) { sigReceiverPad.clear(); sigReceiverPad = null; }
            $('#sigAdminData').val('');
            $('#sigReceiverData').val('');
        });

        $('#formSignature').on('submit', function(e) {
            e.preventDefault();
            const id = $('#sigDistributionId').val();

            if (sigAdminPad && !sigAdminPad.isEmpty()) {
                $('#sigAdminData').val(sigAdminPad.toDataURL());
            } else {
                Swal.fire({ icon: 'warning', title: 'Lengkapi', text: 'Tanda tangan Admin/Purchasing wajib diisi.' });
                return;
            }
            if (sigReceiverPad && !sigReceiverPad.isEmpty()) {
                $('#sigReceiverData').val(sigReceiverPad.toDataURL());
            } else {
                Swal.fire({ icon: 'warning', title: 'Lengkapi', text: 'Tanda tangan Penerima wajib diisi.' });
                return;
            }

            $.ajax({
                url: '/consumable/distributions/' + id + '/signature',
                method: 'POST',
                data: {
                    _token: csrfToken,
                    admin_signature: $('#sigAdminData').val(),
                    receiver_signature: $('#sigReceiverData').val(),
                },
                success: function(res) {
                    $('#modalSignature').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                    table.ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Gagal menyimpan tanda tangan' });
                }
            });
        });

        // --- Print ---
        window.printDistribution = function(id) {
            window.open('/consumable/distributions/' + id + '/print', '_blank');
        };

        // --- Quick Add HeldBy ---
        $('#btnAddHeldBy').on('click', function() {
            $('#modalCreateDistribution').modal('hide');
            $('#modalQuickHeldBy').modal('show');
        });

        $('#modalQuickHeldBy').on('hidden.bs.modal', function() {
            $('#modalCreateDistribution').modal('show');
        });

        $('#formQuickHeldBy').on('submit', function(e) {
            e.preventDefault();
            const name = $('#quickHeldByName').val();
            $.ajax({
                url: '/api/held_bies',
                method: 'POST',
                data: { _token: csrfToken, name: name },
                success: function(res) {
                    $('#modalQuickHeldBy').modal('hide');
                    $('#quickHeldByName').val('');
                    refreshHeldBies(res.data?.id || res.name || name);
                    Swal.fire({ icon: 'success', title: 'Peminta ditambahkan', timer: 1500, showConfirmButton: false });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Error' });
                }
            });
        });

        function refreshHeldBies(selectedName) {
            $.get('/api/held_bies', function(data) {
                const $sel = $('#distRequester');
                $sel.find('option:not([value=""])').remove();
                data.forEach(function(item) {
                    $sel.append('<option value="' + item.name + '">' + item.name + '</option>');
                });
                $sel.val(selectedName);
            });
        }

        // --- Quick Add Division ---
        $('#btnAddDivision').on('click', function() {
            $('#modalCreateDistribution').modal('hide');
            $('#modalQuickDivision').modal('show');
        });

        $('#modalQuickDivision').on('hidden.bs.modal', function() {
            $('#modalCreateDistribution').modal('show');
        });

        $('#formQuickDivision').on('submit', function(e) {
            e.preventDefault();
            const name = $('#quickDivisionName').val();
            $.ajax({
                url: '/api/divisions',
                method: 'POST',
                data: { _token: csrfToken, name: name },
                success: function(res) {
                    $('#modalQuickDivision').modal('hide');
                    $('#quickDivisionName').val('');
                    refreshDivisions(res.id);
                    Swal.fire({ icon: 'success', title: 'Divisi ditambahkan', timer: 1500, showConfirmButton: false });
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: xhr.responseJSON?.message || 'Error' });
                }
            });
        });

        function refreshDivisions(selectedId) {
            $.get('/api/divisions', function(data) {
                const $sel = $('#distDivision');
                $sel.find('option:not([value=""])').remove();
                data.forEach(function(item) {
                    $sel.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                $sel.val(selectedId);
            });
        }
    });
</script>
@endpush
