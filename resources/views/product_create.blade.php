@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8"> {{-- Diperlebar sedikit agar lebih lega di desktop --}}
            <div class="card p-4 border-0 shadow">
                <h3 class="mb-4 text-center fw-bold text-primary">Input Barang Baru</h3>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">SKU (Otomatis)</label>
                        <input type="text" name="sku" class="form-control bg-light" value="{{ $autoSku }}" readonly>
                        <small class="text-muted">Nomor SKU dibuat otomatis oleh sistem.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Barang</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Laptop MacBook Pro" required>
                    </div>

                    <div class="mb-4">
    <label class="fw-bold d-block">Foto Produk (Gallery)</label>
    
    <div id="gallery-preview" class="row g-2 mb-2"></div>

    <button type="button" class="btn btn-outline-primary" onclick="triggerUpload()">
        <i class="bi bi-camera-fill"></i> Pilih Foto / Ambil Kamera
    </button>

    <div id="gallery-inputs" style="display: none;"></div>
    
    <small class="text-muted d-block mt-1">Tips: Bisa pilih banyak foto sekaligus.</small>
</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Kategori</label>
                            <div class="input-group">
                                <select name="category_id" id="category_select" class="form-select">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKategori">+</button>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Divisi</label>
                            <div class="input-group">
                                <select name="division_id" id="division_select" class="form-select">
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach($divisions as $div)
                                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDivisi">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Stok Total</label>
                            <input type="number" name="stock" id="total_stock" class="form-control bg-light fw-bold" value="0" readonly>
                            <small class="text-muted">Terjumlah otomatis dari rincian di bawah.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Harga (Rp)</label>
                            <input type="text" 
                                id="inputHarga" 
                                name="price_display" 
                                class="form-control" 
                                placeholder="Contoh: 1.000.000" 
                                required>
                            <input type="hidden" name="price" id="priceReal">
                        </div>
                    </div>

                    <div class="row bg-light border rounded p-3 mb-4 mx-0 shadow-sm">
                        <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold">📍 Keterangan Pemakaian Aset</h6>
                        
                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold text-muted">Tipe Barang</label>
                            <select name="usage_type" class="form-select form-select-sm">
                                <option value="individual">Individu</option>
                                <option value="shared">Fasilitas Bersama</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold text-muted">Pemegang</label>
                            <div class="input-group input-group-sm">
                                <select name="held_by_id" id="held_by_select" class="form-select">
                                    <option value="">-- Pilih Pemegang --</option>
                                    @foreach($held_bies as $h)
                                        <option value="{{ $h->id }}">{{ $h->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalHeld_by">+</button>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="small fw-bold text-muted">Lokasi / Ruangan</label>
                            <div class="input-group input-group-sm">
                                <select name="location_id" id="location_select" class="form-select">
                                    <option value="">-- Pilih Lokasi --</option>
                                    @foreach($locations as $l)
                                        <option value="{{ $l->id }}">{{ $l->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalLocation">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold">Tanggal Pembelian</label>
                        <input type="date" name="purchase_date" class="form-control" 
                            value="{{ old('purchase_date', isset($product) ? ($product->purchase_date ? \Carbon\Carbon::parse($product->purchase_date)->format('Y-m-d') : '') : date('Y-m-d')) }}">
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold">Masa Garansi Berakhir</label>
                        <input type="date" name="warranty_expiry_date" class="form-control" 
                            value="{{ old('warranty_expiry_date', (isset($product) && $product->warranty_expiry_date) ? \Carbon\Carbon::parse($product->warranty_expiry_date)->format('Y-m-d') : '') }}">
                        <small class="text-muted">Kosongkan jika tidak ada garansi.</small>
                    </div>

                    <div class="row mb-4 text-center">
                        <div class="col-4">
                            <label class="small fw-bold text-success">Ready</label>
                            <input type="number" name="stock_ready" class="form-control form-control-sm unit-input" value="0" min="0">
                        </div>
                        <div class="col-4">
                            <label class="small fw-bold text-warning">Servis</label>
                            <input type="number" name="stock_repair" class="form-control form-control-sm unit-input" value="0" min="0">
                        </div>
                        <div class="col-4">
                            <label class="small fw-bold text-danger">Rusak</label>
                            <input type="number" name="stock_broken" class="form-control form-control-sm unit-input" value="0" min="0">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">💾 Simpan Barang</button>
                        <a href="/products" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection