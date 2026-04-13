<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanner Aset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        #reader { width: 100%; max-width: 500px; margin: auto; border-radius: 10px; overflow: hidden; }
        .result-box { display: none; margin-top: 20px; border-radius: 10px; }
    </style>
</head>
<body class="bg-light">
    <div class="container text-center mt-5">
    <h4>Stok Opname Aset</h4>
    <p>Gunakan kamera HP (mode scan) lalu tempelkan SKU di sini</p>
    
    <form action="{{ route('audit.process') }}" method="POST">
        @csrf
        <input type="text" name="sku" id="sku_input" 
               class="form-control form-control-lg text-center" 
               placeholder="Klik di sini & Scan QR" autofocus>
        
        <button type="submit" class="btn btn-primary mt-3 w-100">Proses Audit</button>
    </form>
</div>

<script>
    // Supaya kursor selalu standby di input, jadi tinggal scan-scan saja
    document.getElementById('sku_input').focus();
</script>

    <script>
        function onScanSuccess(decodedText) {
            html5QrcodeScanner.clear(); // Berhenti scan jika dapat
            
            fetch("{{ route('audit.process') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ sku: decodedText })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    document.getElementById('result-box').style.display = 'block';
                    document.getElementById('res-name').innerText = data.data.name;
                    document.getElementById('res-sku').innerText = 'SKU: ' + data.data.sku;
                } else {
                    alert(data.message);
                    location.reload();
                }
            });
        }

        let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</body>
</html>