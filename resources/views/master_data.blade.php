@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Master Data</h4>
    </div>

    <div class="row g-4">
        {{-- KATEGORI --}}
        <div class="col-lg-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-tag-fill me-2 text-primary"></i>Kategori</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th class="text-end" style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                            <tr>
                                <td>{{ $cat->name }}</td>
                                <td class="text-end">
                                    @if(Auth::user()->role === 'admin')
                                    <button class="btn btn-sm btn-outline-danger" onclick="hapusMaster('categories', {{ $cat->id }}, '{{ $cat->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted text-center py-3">Belum ada kategori</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- DIVISI --}}
        <div class="col-lg-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-building me-2 text-success"></i>Divisi</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th class="text-end" style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($divisions as $div)
                            <tr>
                                <td>{{ $div->name }}</td>
                                <td class="text-end">
                                    @if(Auth::user()->role === 'admin')
                                    <button class="btn btn-sm btn-outline-danger" onclick="hapusMaster('divisions', {{ $div->id }}, '{{ $div->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted text-center py-3">Belum ada divisi</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PEMEGANG --}}
        <div class="col-lg-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-badge me-2 text-warning"></i>Pemegang</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th class="text-end" style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($held_bies as $h)
                            <tr>
                                <td>{{ $h->name }}</td>
                                <td class="text-end">
                                    @if(Auth::user()->role === 'admin')
                                    <button class="btn btn-sm btn-outline-danger" onclick="hapusMaster('held_bies', {{ $h->id }}, '{{ $h->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted text-center py-3">Belum ada pemegang</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- LOKASI --}}
        <div class="col-lg-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-danger"></i>Lokasi</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th class="text-end" style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($locations as $l)
                            <tr>
                                <td>{{ $l->name }}</td>
                                <td class="text-end">
                                    @if(Auth::user()->role === 'admin')
                                    <button class="btn btn-sm btn-outline-danger" onclick="hapusMaster('locations', {{ $l->id }}, '{{ $l->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-muted text-center py-3">Belum ada lokasi</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function hapusMaster(type, id, name) {
    Swal.fire({
        title: 'Hapus ' + name + '?',
        text: 'Data yang sudah dipakai produk tidak bisa dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('/api/' + type + '/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                    location.reload();
                } else {
                    Swal.fire('Error', data.message || 'Gagal menghapus', 'error');
                }
            })
            .catch(e => {
                Swal.fire('Error', e.message || 'Terjadi kesalahan', 'error');
            });
        }
    });
}
</script>
@endpush
@endsection