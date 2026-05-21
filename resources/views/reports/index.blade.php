@extends('layouts.app') @section('content')
<div class="container-fluid">
    <h2 class="fw-bold text-dark mb-4"><i class="bi bi-file-earmark-text me-2"></i>Export Assets Report</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Data</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.export_excel') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Divisi</label>
                        <select name="division_id" class="form-select">
                            <option value="">Semua Divisi</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Lokasi</label>
                        <select name="location_id" class="form-select">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Kondisi</label>
                        <select name="condition" class="form-select">
                            <option value="">Semua Kondisi</option>
                            @foreach($conditions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal Beli (Dari)</label>
                        <input type="date" name="purchase_date_start" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal Beli (Sampai)</label>
                        <input type="date" name="purchase_date_end" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status Produk</label>
                        <select name="include_inactive" class="form-select">
                            <option value="0">Active Only</option>
                            <option value="1" selected>All (include archived/trashed)</option>
                            <option value="2">Archive/Trashed Only</option>
                        </select>
                    </div>
                </div>

                <hr>
                <h6 class="font-weight-bold text-primary mb-3">Pilih Kolom yang Ingin Ditampilkan</h6>
                <div class="row">
                    @php
                        $availableColumns = [
                            'sku' => 'SKU',
                            'name' => 'Name',
                            'category_id' => 'Category',
                            'division_id' => 'Division',
                            'location_id' => 'Location',
                            'held_by_id' => 'Held By',
                            'price' => 'Price',
                            'condition' => 'Condition',
                            'is_active' => 'Status',
                            'supplier_id' => 'Supplier',
                            'purchase_date' => 'Purchase Date',
                            'warranty_expiry_date' => 'Warranty Expiry',
                            'audit_date' => 'Last Audit Date',
                            'auditor_name' => 'Last Auditor',
                            'audit_notes' => 'Audit Notes'
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
