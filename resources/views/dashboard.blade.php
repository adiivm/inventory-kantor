@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard Ringkasan</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" onclick="location.href='{{ route('product.index') }}'" style="cursor: pointer; background-color: #4e73df; color: white;">
                <div class="card-body text-center">
                    <h5 class="small text-uppercase fw-bold" style="opacity: 0.8;">Total Aset Terdaftar</h5>
                    <h3>{{ $totalBarang }} Unit</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" onclick="location.href='{{ route('product.index') }}?condition=ready'" style="cursor: pointer; background-color: #1cc88a; color: white;">
                <div class="card-body text-center">
                    <h5 class="small text-uppercase fw-bold" style="opacity: 0.8;">Aset Kondisi Baik</h5>
                    <h3>{{ $barangReady }} Unit</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" onclick="location.href='{{ route('product.index') }}?condition=repair'" style="cursor: pointer; background-color: #f6c23e; color: white;">
                <div class="card-body text-center">
                    <h5 class="small text-uppercase fw-bold" style="opacity: 0.8;">Aset Butuh Perbaikan & Rusak</h5>
                    <h3>{{ $barangServis + $barangRusak }} Unit</h3>
                </div>
            </div>
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