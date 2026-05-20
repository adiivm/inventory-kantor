<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Audit Berhasil</title>
    <style>
        body { background: #f4f7fe; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .success-card { background: #fff; border-radius: 20px; padding: 30px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .success-icon { font-size: 4rem; color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="success-card">
                    <div class="success-icon mb-3">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h3 class="fw-bold text-success">Audit Berhasil!</h3>
                    <p class="text-muted">Data audit untuk <strong>{{ $product->name }}</strong> telah disimpan.</p>
                    <hr>
                    <p class="small text-muted">Terima kasih telah melakukan audit aset.</p>
                    <a href="{{ url('/audit/direct/' . $product->sku) }}" class="btn btn-primary fw-bold">
                        <i class="bi bi-qr-code me-2"></i> Audit Lagi
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>