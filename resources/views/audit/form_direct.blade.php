<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Audit Aset</title>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="card shadow border-0">
            <div class="card-body text-center">
                <img src="{{ asset('storage/products/'.$product->image) }}" class="img-fluid rounded mb-3" style="max-height: 150px;">
                <h4 class="fw-bold">{{ $product->name }}</h4>
                <p class="text-muted">{{ $product->sku }}</p>
                <hr>
                
                <form action="{{ route('audit.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold">Tanggal Audit</label>
                        <input type="date" name="audit_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold">Keterangan / Kondisi</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Barang baik, sedang dipinjam, atau perlu servis">{{ $product->internal_notes }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold">SUBMIT DATA AUDIT</button>
                    
                </form>
            </div>
        </div>
    </div>
</body>
</html>