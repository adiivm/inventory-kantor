@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">

            {{-- Header --}}
            <div class="mb-4">
                <h2 class="fw-bold text-dark mb-1">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard Consumable
                </h2>
                <p class="text-muted mb-0">
                    Pantau stok, barang kritis, dan tren pemakaian ATK kantor
                </p>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- ROW 1 — HEALTH INDICATORS (KPI Cards)                       --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-box-seam text-primary fs-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fs-4 fw-bold text-dark">{{ $totalItems }}</div>
                                <div class="text-muted small text-truncate">Total Barang</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-exclamation-triangle text-danger fs-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fs-4 fw-bold text-danger">{{ $lowStockCount }}</div>
                                <div class="text-muted small text-truncate">Stok Menipis</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-clock-history text-warning fs-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fs-4 fw-bold text-warning">{{ $pendingDistributionCount }}</div>
                                <div class="text-muted small text-truncate">Distribusi Pending</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 py-3">
                            <div class="rounded-3 bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-arrow-left-right text-success fs-5"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fs-4 fw-bold text-success">{{ $transactionsToday }}</div>
                                <div class="text-muted small text-truncate">Transaksi Hari Ini</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- ROW 2 — ACTION CENTER (Focus Area)                          --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                {{-- Urgent Restock Needed --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                            <h6 class="fw-bold mb-0 text-danger">
                                <i class="bi bi-basket me-1"></i> Urgent Restock Needed
                            </h6>
                            <a href="{{ route('consumable.items') }}" class="btn btn-sm btn-outline-danger">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            @if($urgentItems->isEmpty())
                                <p class="text-muted text-center py-4 mb-0">Semua stok dalam kondisi aman.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless align-middle mb-0">
                                        <thead class="text-muted small bg-light">
                                            <tr>
                                                <th class="ps-3">Barang</th>
                                                <th class="text-center">Stok</th>
                                                <th class="text-center">Min</th>
                                                <th class="text-end pe-3">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($urgentItems as $item)
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="fw-semibold small">{{ $item->name }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $item->current_stock <= 0 ? 'danger' : 'warning text-dark' }}">
                                                        {{ $item->current_stock }}
                                                    </span>
                                                </td>
                                                <td class="text-center text-muted">{{ $item->min_stock }}</td>
                                                <td class="text-end pe-3">
                                                    <a href="{{ route('consumable.transactions') }}?item_id={{ $item->id }}&type=in" class="btn btn-sm btn-success">
                                                        <i class="bi bi-plus-circle"></i> Tambah Stok
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Recent Pending Approvals --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                            <h6 class="fw-bold mb-0 text-warning">
                                <i class="bi bi-clock-history me-1"></i> Recent Pending Approvals
                            </h6>
                            <a href="{{ route('consumable.distributions', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            @if($pendingDistributions->isEmpty())
                                <p class="text-muted text-center py-4 mb-0">Tidak ada permintaan pending.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless align-middle mb-0">
                                        <thead class="text-muted small bg-light">
                                            <tr>
                                                <th class="ps-3">Ref</th>
                                                <th>Peminta</th>
                                                <th>Divisi</th>
                                                <th class="text-end pe-3">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingDistributions as $d)
                                            <tr>
                                                <td class="ps-3"><span class="fw-semibold small">{{ $d->reference_number }}</span></td>
                                                <td class="small">{{ $d->requester_name }}</td>
                                                <td class="small">{{ $d->division?->name ?? '-' }}</td>
                                                <td class="text-end pe-3">
                                                    <a href="{{ route('consumable.distributions', ['status' => 'pending']) }}" class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-lg"></i> Approve
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- ROW 3 — VISUAL ANALYTICS                                     --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                {{-- Consumable Outflow Trend (Line Chart) --}}
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="fw-bold mb-0">
                                <i class="bi bi-graph-down me-1"></i> Consumable Outflow Trend (7 Hari)
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="outflowChart" height="220"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Top 5 Requested Items (Bar Chart) --}}
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="fw-bold mb-0">
                                <i class="bi bi-bar-chart-fill me-1"></i> Top 5 Requested Items
                            </h6>
                        </div>
                        <div class="card-body d-flex align-items-center">
                            <canvas id="topRequestedChart" height="220"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // ── Line Chart: Outflow Trend ──
    var ctx1 = document.getElementById('outflowChart');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: {!! $outflowLabels->toJson() !!},
                datasets: [{
                    label: 'Barang Keluar',
                    data: {!! $outflowData->toJson() !!},
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: '#dc3545',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0,0,0,0.04)' },
                    },
                    x: {
                        grid: { display: false },
                    }
                }
            }
        });
    }

    // ── Bar Chart: Top 5 Requested Items ──
    var labels = {!! $topRequested->pluck('consumableItem.name')->toJson() !!};
    var values = {!! $topRequested->pluck('total_qty')->toJson() !!};
    var ctx2 = document.getElementById('topRequestedChart');

    if (ctx2) {
        if (labels.length > 0) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Diminta',
                        data: values,
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.75)',
                            'rgba(25, 135, 84, 0.75)',
                            'rgba(255, 193, 7, 0.75)',
                            'rgba(220, 53, 69, 0.75)',
                            'rgba(111, 66, 193, 0.75)',
                        ],
                        borderColor: [
                            '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1',
                        ],
                        borderWidth: 1,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                            grid: { color: 'rgba(0,0,0,0.04)' },
                        },
                        y: {
                            grid: { display: false },
                        }
                    }
                }
            });
        } else {
            ctx2.parentElement.innerHTML = '<p class="text-muted text-center w-100 mb-0">Belum ada data permintaan.</p>';
        }
    }
});
</script>
@endpush
