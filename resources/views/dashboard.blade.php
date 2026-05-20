@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h2 class="fw-bold text-dark">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </h2>
        <p class="text-muted mb-0">Company inventory data summary</p>
    </div>

    <!-- Row 1: Kondisi Aset -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm transition-card h-100" onclick="location.href='{{ route('product.index') }}'" style="cursor: pointer; background-color: #e8f0fe; border-left: 5px solid #4e73df !important;">
                <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-1" style="color: #4e73df; font-size: 0.75rem;">Total Aset Terdaftar</h6>
                        <p class="mb-0 small text-muted">Seluruh inventaris</p>
                    </div>
                    <h3 class="fw-bold mb-0" style="color: #4e73df;">{{ $totalBarang }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm transition-card h-100" onclick="location.href='{{ route('product.index') }}?condition=ready'" style="cursor: pointer; background-color: #d1f7ea; border-left: 5px solid #1cc88a !important;">
                <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-1" style="color: #1cc88a; font-size: 0.75rem;">Kondisi Baik</h6>
                        <p class="mb-0 small text-muted">Siap digunakan</p>
                    </div>
                    <h3 class="fw-bold mb-0" style="color: #1cc88a;">{{ $barangReady }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm transition-card h-100" onclick="location.href='{{ route('product.index') }}?condition=repair'" style="cursor: pointer; background-color: #fff3e0; border-left: 5px solid #fd7e14 !important;">
                <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-1" style="color: #fd7e14; font-size: 0.75rem;">Perbaikan / Rusak</h6>
                        <p class="mb-0 small text-muted">Butuh tindakan</p>
                    </div>
                    <h3 class="fw-bold mb-0" style="color: #fd7e14;">{{ $barangServis + $barangRusak }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm transition-card h-100" onclick="location.href='{{ route('product.trash') }}'" style="cursor: pointer; background-color: #f1f3f4; border-left: 5px solid #858796 !important;">
                <div class="card-body d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h6 class="text-uppercase fw-bold mb-1" style="color: #858796; font-size: 0.75rem;">Aset Archive</h6>
                        <p class="mb-0 small text-muted">Data dihapus</p>
                    </div>
                    <h3 class="fw-bold mb-0" style="color: #858796;">{{ $barangArchive }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Tracking Garansi -->
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-exclamation me-2"></i>Tracking Garansi Aset</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <a href="{{ route('product.index', ['warranty_status' => 'critical']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm transition-card" style="background-color: #fff3cd; border-left: 5px solid #ffc107 !important; color: #856404;">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1 small text-muted">Garansi Kritis</h6>
                            <p class="mb-0 small text-dark">Berakhir dalam kurun waktu &le; 30 hari</p>
                        </div>
                        <h2 class="fw-bold mb-0 text-warning">{{ $garansiKritis ?? 0 }} <span class="fs-5 text-muted">Unit</span></h2>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('product.index', ['warranty_status' => 'expired']) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm transition-card" style="background-color: #f8d7da; border-left: 5px solid #dc3545 !important; color: #721c24;">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1 small text-muted">Garansi Kedaluwarsa</h6>
                            <p class="mb-0 small text-dark">Masa perlindungan garansi resmi sudah lewat</p>
                        </div>
                        <h2 class="fw-bold mb-0 text-danger">{{ $garansiExpired ?? 0 }} <span class="fs-5 text-muted">Unit</span></h2>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card p-4 shadow-sm">
                <h5 class="text-center mb-3">Sebaran Barang Per Kategori</h5>
                <div style="height: 300px;"> 
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card p-4 h-100 shadow-sm">
                <h4>Halo, {{ Auth::user()->name }}!</h4>
                <p>Hari ini adalah <strong>{{ date('l, d F Y') }}</strong>.</p>
                <p>Sistem inventaris memantau <strong>{{ $totalBarang }}</strong> aset saat ini.</p>
                <hr>
                <h6>Ringkasan Kondisi Unit:</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-check-circle text-success me-2"></i> Ready (Layak Pakai)</span>
                        <span class="badge rounded-pill shadow-sm" style="background-color: #1cc88a;">{{ $barangReady }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-tools text-warning me-2"></i> Sedang Servis</span>
                        <span class="badge rounded-pill shadow-sm" style="background-color: #f6c23e;">{{ $barangServis }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-times-circle text-danger me-2"></i> Rusak Berat</span>
                        <span class="badge rounded-pill shadow-sm" style="background-color: #e74a3b;">{{ $barangRusak }}</span>
                    </li>
                </ul>
                <a href="{{ route('product.index') }}" class="btn btn-outline-primary mt-4">Lihat Detail Semua Barang</a>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12 mb-4">
            <div class="card p-4 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-uppercase fw-bold" style="opacity: 0.9;">Total Nilai Asset</h6>
                        <h2 class="fw-bold">Rp{{ number_format($totalNilaiAsset, 0, ',', '.') }}</h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <i class="fas fa-chart-line" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <hr style="border-color: rgba(255,255,255,0.3);">
                <p class="small mb-0">Nilai total asset berdasarkan harga input dan jumlah stok yang tersedia di sistem.</p>
            </div>
        </div>
    </div>
</div> 

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [{
                    label: 'Jumlah Barang',
                    data: {!! json_encode($data) !!},
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'
                    ],
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });
</script>
@endsection