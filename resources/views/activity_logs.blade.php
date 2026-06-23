@extends('layouts.app')

@push('styles')
<style>
    #tableLogs tbody td { vertical-align: middle; }
    .diff-table { font-size: 0.85rem; }
    .diff-table td { padding: 4px 8px; }
    .diff-old { background: #f8d7da; }
    .diff-new { background: #d1e7dd; }
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
                            <i class="bi bi-activity me-2"></i>Riwayat Aktivitas
                        </h2>
                        <p class="text-muted mb-0">Catat perubahan yang terjadi di seluruh modul</p>
                    </div>
                </div>

                {{-- Filter Row --}}
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small mb-1">Modul</label>
                        <select id="filter_module" class="form-select form-select-sm">
                            <option value="">Semua Modul</option>
                            <option value="asset">Asset</option>
                            <option value="consumable">Consumable</option>
                            <option value="distribution">Distribusi</option>
                            <option value="master">Master Data</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold text-muted small mb-1">Aksi</label>
                        <select id="filter_action" class="form-select form-select-sm">
                            <option value="">Semua Aksi</option>
                            <option value="create">Create</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                            <option value="archive">Archive</option>
                            <option value="restore">Restore</option>
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
                    <table class="table table-hover align-middle" id="tableLogs" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="14%">Waktu</th>
                                <th width="12%">User</th>
                                <th width="10%">Modul</th>
                                <th width="10%">Aksi</th>
                                <th width="34%">Deskripsi</th>
                                <th width="8%">Detail</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-info text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Detail Perubahan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailBody">
                <div class="text-center py-4 text-muted">Memuat...</div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function getFilterParams() {
            return {
                module: $('#filter_module').val(),
                action: $('#filter_action').val(),
                date_start: $('#filter_date_start').val(),
                date_end: $('#filter_date_end').val(),
            };
        }

        const table = $('#tableLogs').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('activity.logs') }}",
                data: function(d) {
                    Object.assign(d, getFilterParams());
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'user_name', name: 'user_name' },
                { data: 'module_badge', name: 'module', orderable: true, searchable: false },
                { data: 'action_badge', name: 'action', orderable: true, searchable: false },
                { data: 'description', name: 'description' },
                { data: 'detail', name: 'detail', orderable: false, searchable: false },
            ],
            language: {
                search: "",
                searchPlaceholder: "Cari aktivitas...",
            },
            order: [[1, 'desc']],
        });

        $('#filter_module, #filter_action, #filter_date_start, #filter_date_end').change(function() {
            table.ajax.reload();
        });

        $('#btnResetFilter').click(function() {
            $('#filter_module, #filter_action').val('');
            $('#filter_date_start, #filter_date_end').val('');
            table.ajax.reload();
        });

        // ── Detail modal ──
        $('#tableLogs').on('click', '.btn-detail', function() {
            const id = $(this).data('id');
            $('#modalDetailBody').html('<div class="text-center py-4 text-muted">Memuat...</div>');
            $('#modalDetail').modal('show');

            $.get(`/activity-logs/${id}`, function(log) {
                let html = '';

                const oldVal = log.old_values && typeof log.old_values === 'string' ? JSON.parse(log.old_values) : log.old_values;
                const newVal = log.new_values && typeof log.new_values === 'string' ? JSON.parse(log.new_values) : log.new_values;

                if (oldVal || newVal) {
                    const keys = new Set([
                        ...Object.keys(oldVal || {}),
                        ...Object.keys(newVal || {}),
                    ]);

                    const ignoreKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];
                    const labels = {
                        name: 'Nama', email: 'Email', role: 'Role', can_approve: 'Can Approve',
                        current_stock: 'Stok', min_stock: 'Min Stok', unit: 'Satuan',
                        category_id: 'Kategori', supplier_id: 'Supplier',
                        status: 'Status', qty: 'Qty', type: 'Tipe',
                        reference_number: 'Ref Number', date: 'Tanggal',
                        description: 'Deskripsi', notes: 'Keterangan',
                        requester_name: 'Peminta', division_id: 'Divisi',
                        price: 'Harga', sku: 'SKU', stock: 'Stok',
                        condition: 'Kondisi', location_id: 'Lokasi', held_by_id: 'Pemegang',
                        purchase_date: 'Tgl Pembelian', warranty_expiry_date: 'Masa Garansi',
                        warranty_start_date: 'Tgl Garansi Mulai',
                        last_audited_at: 'Audit Terakhir', approved_at: 'Disetujui',
                        received_at: 'Diterima', approved_by: 'Disetujui Oleh',
                        admin_signature: 'TTD Admin', receiver_signature: 'TTD Penerima',
                        is_active: 'Status Aktif', usage_type: 'Tipe Pemakaian',
                        audit_date: 'Tgl Audit', auditor_name: 'Auditor',
                    };

                    function formatValue(val, key) {
                        if (val === null || val === undefined) return '<em class="text-muted">-</em>';
                        if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/.test(val)) {
                            const parts = val.split('T');
                            const datePart = parts[0];
                            const [y, m, day] = datePart.split('-');
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                            const dateStr = `${parseInt(day)} ${months[parseInt(m) - 1]} ${y}`;
                            // Hanya tampilkan jam untuk field yang memang menyimpan waktu
                            const timeFields = ['approved_at', 'received_at', 'last_audited_at', 'created_at', 'updated_at', 'audit_date'];
                            if (key && timeFields.includes(key)) {
                                const hm = parts[1].split(':');
                                return `${dateStr}, ${hm[0]}.${hm[1]}`;
                            }
                            return dateStr;
                        }
                        if (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
                            const [y, m, day] = val.split('-');
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                            return `${parseInt(day)} ${months[parseInt(m) - 1]} ${y}`;
                        }
                        return val;
                    }

                    html += '<table class="table table-sm diff-table mb-0">';
                    html += '<thead><tr><th>Field</th><th class="diff-old">Nilai Lama</th><th class="diff-new">Nilai Baru</th></tr></thead><tbody>';

                    for (const key of keys) {
                        if (ignoreKeys.includes(key)) continue;
                        const old = formatValue(oldVal?.[key], key);
                        const neu = formatValue(newVal?.[key], key);
                        const label = labels[key] || key;
                        const changed = JSON.stringify(oldVal?.[key]) !== JSON.stringify(newVal?.[key]);
                        html += `<tr>
                            <td><strong>${label}</strong></td>
                            <td class="diff-old${changed ? '' : ' text-muted'}">${old}</td>
                            <td class="diff-new${changed ? '' : ' text-muted'}">${neu}</td>
                        </tr>`;
                    }

                    html += '</tbody></table>';
                } else {
                    html = '<p class="text-muted text-center mb-0">Tidak ada data perubahan untuk ditampilkan.</p>';
                }

                $('#modalDetailBody').html(html);
            }).fail(function() {
                $('#modalDetailBody').html('<p class="text-danger text-center mb-0">Gagal memuat detail.</p>');
            });
        });
    });
</script>
@endpush
