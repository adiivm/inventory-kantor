@extends('layouts.app') @section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Export Laporan Aset (Custom Excel)</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pilih Kolom yang Ingin Ditampilkan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.export_excel') }}" method="POST">
                @csrf
                <div class="row">
                    @php
                        // Daftar kolom yang diizinkan untuk diexport
                        $availableColumns = [
                            'sku' => 'SKU Barang',
                            'name' => 'Nama Barang',
                            'category_id' => 'Kategori',
                            'division_id' => 'Divisi',
                            'location_id' => 'Lokasi',
                            'held_by_id' => 'Dipegang Oleh',
                            'price' => 'Harga',
                            'stock' => 'Total Stok',
                            'stock_ready' => 'Stok Ready',
                            'stock_repair' => 'Stok Repair',
                            'stock_broken' => 'Stok Rusak',
                            'purchase_date' => 'Tanggal Beli',
                            'warranty_expiry_date' => 'Masa Garansi'
                        ];
                    @endphp

                    @foreach($availableColumns as $key => $label)
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $key }}" id="col_{{ $key }}" checked>
                                <label class="form-check-label cursor-pointer" for="col_{{ $key }}">
                                    {{ $label }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <hr>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i> Download Excel
                </button>
            </form>
        </div>
    </div>
</div>
@endsection