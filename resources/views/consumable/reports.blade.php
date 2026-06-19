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
                            <div class="fs-3 fw-bold text-primary">{{ $items->count() }}</div>
                            <div class="text-muted small">Total Barang</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm bg-success bg-opacity-10 p-3 text-center">
                            <div class="fs-3 fw-bold text-success">{{ $items->filter(fn($i) => $i->current_stock > $i->min_stock)->count() }}</div>
                            <div class="text-muted small">Stok Aman</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm bg-warning bg-opacity-10 p-3 text-center">
                            <div class="fs-3 fw-bold text-warning">{{ $items->filter(fn($i) => $i->current_stock > 0 && $i->current_stock <= $i->min_stock)->count() }}</div>
                            <div class="text-muted small">Stok Menipis</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm bg-danger bg-opacity-10 p-3 text-center">
                            <div class="fs-3 fw-bold text-danger">{{ $items->where('current_stock', 0)->count() }}</div>
                            <div class="text-muted small">Stok Habis</div>
                        </div>
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
                        <tbody>
                            @foreach($items as $i => $item)
                            @php
                                $status = 'Aman';
                                $badge = 'bg-success';
                                if ($item->current_stock <= 0) { $status = 'Habis'; $badge = 'bg-danger'; }
                                elseif ($item->current_stock <= $item->min_stock) { $status = 'Menipis'; $badge = 'bg-warning text-dark'; }
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category?->name ?? '-' }}</td>
                                <td>{{ $item->current_stock }}</td>
                                <td>{{ $item->min_stock }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>{{ $item->supplier?->name ?? $item->supplier_name ?? '-' }}</td>
                                <td><span class="badge {{ $badge }}">{{ $status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
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
        $('#tableReport').DataTable({
            language: {
                search: "",
                searchPlaceholder: "Cari barang...",
            },
            order: [[1, 'asc']],
        });
    });
</script>
@endpush
