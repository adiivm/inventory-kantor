@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card p-4 border-0 shadow-sm">
                <div class="row mb-4">
                    <div class="col">
                        <h2 class="fw-bold text-dark">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan Stok Consumable
                        </h2>
                        <p class="text-muted mb-0">Rekap seluruh barang habis pakai dan status stok</p>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <form action="{{ route('consumable.reports.export') }}" method="POST">
                            @csrf
                            <button class="btn btn-success fw-bold px-3">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                            </button>
                        </form>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm bg-primary bg-opacity-10 p-3 text-center">
                            <div class="fs-3 fw-bold text-primary">{{ $allItems->count() }}</div>
                            <div class="text-muted small">Total Barang</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm bg-success bg-opacity-10 p-3 text-center">
                            <div class="fs-3 fw-bold text-success">{{ $allItems->filter(fn($i) => $i->current_stock > $i->min_stock)->count() }}</div>
                            <div class="text-muted small">Stok Aman</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm bg-warning bg-opacity-10 p-3 text-center">
                            <div class="fs-3 fw-bold text-warning">{{ $allItems->filter(fn($i) => $i->current_stock > 0 && $i->current_stock <= $i->min_stock)->count() }}</div>
                            <div class="text-muted small">Stok Menipis</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm bg-danger bg-opacity-10 p-3 text-center">
                            <div class="fs-3 fw-bold text-danger">{{ $allItems->where('current_stock', 0)->count() }}</div>
                            <div class="text-muted small">Stok Habis</div>
                        </div>
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
                            <option value="aman">Aman</option>
                            <option value="menipis">Menipis</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3 d-flex align-items-end">
                        <button id="btnResetFilter" class="btn btn-sm btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tableReport" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Stok Saat Ini</th>
                                <th>Min. Stok</th>
                                <th>Satuan</th>
                                <th>Supplier</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
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
                category_id: $('#filter_category').val(),
                supplier_id: $('#filter_supplier').val(),
                stock_status: $('#filter_stock_status').val(),
            };
        }

        const table = $('#tableReport').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('consumable.reports') }}",
                data: function(d) {
                    Object.assign(d, getFilterParams());
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'category_name', name: 'category.name', defaultContent: '-' },
                { data: 'current_stock', name: 'current_stock' },
                { data: 'min_stock', name: 'min_stock' },
                { data: 'unit', name: 'unit' },
                { data: 'supplier_name', name: 'supplier.name', defaultContent: '-' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            ],
            language: {
                search: "",
                searchPlaceholder: "Cari barang...",
            },
            order: [[1, 'asc']],
        });

        $('#filter_category, #filter_supplier, #filter_stock_status').change(function() {
            table.ajax.reload();
        });

        $('#btnResetFilter').click(function() {
            $('#filter_category, #filter_supplier, #filter_stock_status').val('');
            table.ajax.reload();
        });
    });
</script>
@endpush