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
                <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data" id="formProduct">
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
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Harga (Rp)</label>
                            <input type="text" 
                                id="inputHarga" 
                                name="price_display" 
                                class="form-control" 
                                placeholder="Contoh: 1.000.000">
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
                    </div>

                    <div class="card border rounded-3 p-3 mb-4 bg-light">
                        <h5 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>Informasi Supplier & Pembelian</h5>
                        
                        <div class="mb-3">
                            <label class="fw-bold">Supplier</label>
                            <div class="input-group">
                                <select name="supplier_id" id="supplier_select" class="form-select">
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSupplier">+</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="fw-bold">Tanggal Pembelian</label>
                                <input type="date" name="purchase_date" class="form-control" 
                                    value="{{ old('purchase_date', isset($product) ? ($product->purchase_date ? \Carbon\Carbon::parse($product->purchase_date)->format('Y-m-d') : '') : date('Y-m-d')) }}">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="fw-bold">Masa Garansi</label>
                                <input type="date" name="warranty_expiry_date" class="form-control" 
                                    value="{{ old('warranty_expiry_date', (isset($product) && $product->warranty_expiry_date) ? \Carbon\Carbon::parse($product->warranty_expiry_date)->format('Y-m-d') : '') }}">
                                <small class="text-muted">Kosongkan jika tidak ada</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold d-block">Kondisi</label>
                            <div class="btn-group w-100" role="group" aria-label="Kondisi">
                                <input type="radio" class="btn-check" name="condition" id="condition_ready" value="ready" autocomplete="off" checked>
                                <label class="btn btn-outline-success btn-sm" for="condition_ready">Ready</label>
                                
                                <input type="radio" class="btn-check" name="condition" id="condition_repair" value="repair" autocomplete="off">
                                <label class="btn btn-outline-warning btn-sm" for="condition_repair">Servis</label>
                                
                                <input type="radio" class="btn-check" name="condition" id="condition_broken" value="broken" autocomplete="off">
                                <label class="btn btn-outline-danger btn-sm" for="condition_broken">Rusak</label>
                                
                                <input type="radio" class="btn-check" name="condition" id="condition_disposed" value="disposed" autocomplete="off">
                                <label class="btn btn-outline-secondary btn-sm" for="condition_disposed">Dibuang</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold flex-grow-1">
                            <i class="bi bi-save me-1"></i> Simpan Barang
                        </button>
                        <a href="/products" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-circle me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.getElementById('formProduct').addEventListener('submit', function(e) {
    const category = document.getElementById('category_select').value;
    const division = document.getElementById('division_select').value;
    const errors = [];

    if (!category) errors.push('Kategori');
    if (!division) errors.push('Divisi');

    if (errors.length) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Lengkapi Data',
            text: 'Mohon pilih ' + errors.join(' & ') + ' terlebih dahulu!',
            confirmButtonColor: '#3085d6'
        });
    }
});
</script>
@endpush
@endsection