<div class="row" id="printableArea">
    <div class="col-4 text-center border-end">
        <!-- <div class="p-2 border rounded bg-light mb-3">
            @if($product->image)
                <img src="{{ asset('storage/products/'.$product->image) }}" class="img-fluid rounded shadow-sm">
            @else
                <div class="py-5 text-muted"><i class="fas fa-image fa-4x"></i><br>Tidak ada foto</div>
            @endif
        </div> -->
        <div id="productCarousel" class="carousel slide border rounded mb-3" data-bs-ride="carousel">
            <div class="carousel-inner text-center bg-light" style="max-height: 350px;">
                @forelse($product->images as $index => $img)
                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/products/' . $img->image_path) }}" 
                            class="d-block mx-auto img-fluid" style="height: 350px; object-fit: contain;">
                    </div>
                @empty
                    <div class="carousel-item active">
                        <img src="{{ asset('images/no-image.png') }}" class="d-block mx-auto img-fluid" style="height: 350px;">
                    </div>
                @endforelse
            </div>
            @if($product->images->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-dark rounded-circle"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle"></span>
                </button>
            @endif
        </div>
        <h4 class="fw-bold text-primary">{{ $product->sku }}</h4>
        <div class="badge bg-{{ $product->is_active == 'active' ? 'success' : 'danger' }} mb-2">
            Status: {{ ucfirst($product->is_active) }}
        </div>
    </div>
    <div class="col-8">
        <table class="table table-sm table-borderless">
            <tr><th class="bg-light" width="30%">Nama Barang</th><td>: {{ $product->name }}</td></tr>
            <tr><th class="bg-light">Kategori</th><td>: {{ $product->category->name ?? 'Tanpa Kategori' }}</td></tr>
            <tr><th class="bg-light">Divisi</th><td>: {{ $product->division->name ?? 'Tanpa Divisi' }}</td></tr>
            <tr><th class="bg-light">Harga Beli</th><td>: Rp {{ number_format($product->price, 0, ',', '.') }}</td></tr>
            <tr><th class="bg-light">Tgl Pembelian</th><td>: {{ date('d F Y', strtotime($product->purchase_date)) }}</td></tr>
            <tr><td colspan="2"><hr></td></tr>
            <tr><th class="bg-light">Kondisi Ready</th><td>: <span class="badge bg-success">{{ $product->stock_ready }}</span></td></tr>
            <tr><th class="bg-light">Kondisi Servis</th><td>: <span class="badge bg-warning">{{ $product->stock_repair }}</span></td></tr>
            <tr><th class="bg-light">Kondisi Rusak</th><td>: <span class="badge bg-danger">{{ $product->stock_broken }}</span></td></tr>
            <tr><td colspan="2"><hr></td></tr>
            <tr><th class="bg-light">Pemegang</th><td>: {{ $product->held_by->name ?? '-' }}</td></tr>
            <tr><th class="bg-light">Lokasi</th><td>: {{ $product->location->name ?? '-' }}</td></tr>
            <tr><th class="bg-light">Tipe</th><td>: {{ ucfirst($product->usage_type) }}</td></tr>
            <tr><th class="bg-light">Tgl Dibuat</th><td>: {{ date('d F Y', strtotime($product->created_at)) }}</td></tr>
            <tr><th class="bg-light">Tgl Diperbarui</th><td>: {{ date('d F Y', strtotime($product->updated_at)) }}</td></tr>
            <tr><th class="bg-light">Tgl Diaudit</th><td>: {{ $product->last_audited_at ? \Carbon\Carbon::parse($product->last_audited_at)->translatedFormat('d F Y') : 'Belum Pernah Di Audit' }}</td></tr>
        </table>
    </div>
    <div class="col-12 mt-4 pt-3 border-top">
        <div class="d-flex justify-content-between small text-muted">
            <span>ID Sistem: #{{ $product->id }}</span>
            <span>Dicetak pada: {{ date('d/m/Y H:i') }}</span>
        </div>
    </div>
</div>

