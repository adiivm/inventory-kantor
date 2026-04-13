
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Login - Inventory System</title>
</head>
<!--<body class="bg-light">-->
<body style="background-image: url('{{ asset('images/Home.jpg') }}'); background-repeat: no-repeat; background-size: cover; background-attachment: fixed;">
<div class="container mt-5">
    
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4 mt-5">
                <div style="text-align: center;">
                    <img src="{{ asset('images/ivans_motor.png') }}" alt="Logo Perusahaan" width="150" height="auto">
                </div>
                <h3 class="text-center mb-4">LOGIN</h3>
                
                @if(session('loginError'))
                    <div class="alert alert-danger">{{ session('loginError') }}</div>
                @endif

                <form action="/login" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Masuk Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>