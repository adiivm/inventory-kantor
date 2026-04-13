@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Riwayat Aktivitas: {{ $product->name }}</h5>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                @forelse($product->logs()->latest()->get() as $log)
                    <li class="list-group-item">
                        <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small> <br>
                        <strong>{{ $log->action }}</strong>: {{ $log->description }} 
                        <span class="badge bg-light text-dark float-end">Oleh: {{ $log->user_name }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-center">Belum ada riwayat untuk barang ini.</li>
                @endforelse
            </ul>
        </div>
        <div class="card-footer">
            <a href="/products" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
</div>
@endsection