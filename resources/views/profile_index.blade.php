@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Waduh!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<h2 class="fw-bold text-dark mb-4"><i class="bi bi-person-circle me-2"></i>My Profile</h2>

<div class="container">
    <div class="row">
        <div class="col-md-4 text-center">
            <div class="card p-3">
                @if(Auth::user()->photo)
                    <img src="{{ asset('storage/photos/' . Auth::user()->photo) }}" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}" class="rounded-circle" width="150">
                @endif
                <h4 class="mt-3">{{ Auth::user()->name }}</h4>
                <p class="text-muted">{{ strtoupper(Auth::user()->role) }}</p>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card p-4">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ Auth::user()->name }}">
                    </div>
                    
                    <div class="mb-3">
                        <label>Ganti Foto Profil</label>
                        <input type="file" name="photo" class="form-control">
                        <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                    </div>
                    <button class="btn btn-primary">Simpan Profil</button>
                </form>
                <div class="card mt-4 p-4">
                        <h5>Form Ganti Password</h5>
                        <hr>
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label>Password Saat Ini</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label>Password Baru</label>
                                <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror">
                                @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label>Konfirmasi Password Baru</label>
                                <input type="password" name="new_password_confirmation" class="form-control">
                            </div>

                            <button class="btn btn-danger">Update Password</button>
                        </form>
                    </div>
            </div>
        </div>
    </div>
</div>


<script>
    // Cari elemen alert, lalu hilangkan setelah 3 detik
    setTimeout(function() {
        let alert = document.querySelector('.alert');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 3000);
</script>
@endsection