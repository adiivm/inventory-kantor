@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">

            {{-- Header --}}
            <div class="mb-4">
                <a href="{{ route('consumable.items') }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h2 class="fw-bold text-dark mb-1">
                    <i class="bi bi-clock-history me-2"></i>Kartu Stok
                </h2>
            </div>

            {{-- Info Barang --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md">
                            <h5 class="fw-bold mb-1">{{ $item->name }}</h5>
                            <div class="text-muted small">
                                <span class="me-3"><i class="bi bi-tag me-1"></i>{{ $item->category?->name ?? '-' }}</span>
                                <span class="me-3"><i class="bi bi-box-seam me-1"></i>Satuan: {{ $item->unit }}</span>
                                <span><i class="bi bi-exclamation-triangle me-1"></i>Min. Stok: {{ $item->min_stock }}</span>
                            </div>
                        </div>
                        <div class="col-md-auto text-md-end">
                            <div class="text-muted small mb-1">Sisa Stok Saat Ini</div>
                            <span class="fs-3 fw-bold {{ $item->current_stock <= $item->min_stock ? 'text-danger' : 'text-success' }}">
                                {{ $item->current_stock }}
                            </span>
                            <span class="text-muted small"> {{ $item->unit }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Riwayat --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-ul me-1"></i>Riwayat Transaksi</h6>
                </div>
                <div class="card-body p-0">
                    @if($transactions->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">Belum ada transaksi untuk barang ini.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" id="historyTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th>Tipe</th>
                                        <th>Qty</th>
                                        <th>Sisa Stok</th>
                                        <th class="pe-3">Referensi / Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $t)
                                    @php
                                        $badge = match($t->type) {
                                            'in' => 'bg-success',
                                            'out' => 'bg-danger',
                                            'adjustment' => 'bg-warning text-dark',
                                            default => 'bg-secondary',
                                        };
                                        $label = match($t->type) {
                                            'in' => 'Masuk',
                                            'out' => 'Keluar',
                                            'adjustment' => 'Penyesuaian',
                                            default => ucfirst($t->type),
                                        };
                                        $referensi = $t->notes ?? $t->reference_number ?? '-';
                                        if (isset($t->distribution_info)) {
                                            $h = $t->distribution_info;
                                            $referensi = 'Distribusi: ' . $h->reference_number .
                                                ' (' . $h->requester_name . ($h->relationLoaded('division') && $h->division ? ' - ' . $h->division->name : '') . ')';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-nowrap small">{{ $t->date ? \Carbon\Carbon::parse($t->date)->format('d/m/Y') : $t->created_at->format('d/m/Y') }}</td>
                                        <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                                        <td>{{ $t->qty }}</td>
                                        <td class="fw-semibold">{{ $t->running_balance }}</td>
                                        <td class="pe-3 small text-muted">{{ $referensi }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#historyTable').DataTable({
            language: {
                search: '',
                searchPlaceholder: 'Cari...',
            },
            order: [[0, 'desc']],
        });
    });
</script>
@endpush
