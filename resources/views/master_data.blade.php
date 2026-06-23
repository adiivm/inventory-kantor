@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">Master Data</h4>
    </div>

    <div class="row g-4">
        @php
            $tables = [
                ['id' => 'categories', 'label' => 'Kategori', 'icon' => 'bi-tag-fill', 'color' => 'primary', 'data' => $categories],
                ['id' => 'divisions', 'label' => 'Divisi', 'icon' => 'bi-building', 'color' => 'success', 'data' => $divisions],
                ['id' => 'held_bies', 'label' => 'Pemegang', 'icon' => 'bi-person-badge', 'color' => 'warning', 'data' => $held_bies],
                ['id' => 'locations', 'label' => 'Lokasi', 'icon' => 'bi-geo-alt', 'color' => 'danger', 'data' => $locations],
                ['id' => 'consumable-categories', 'label' => 'Kategori Consumable', 'icon' => 'bi-folder', 'color' => 'info', 'data' => $consumableCategories],
                ['id' => 'consumable-units', 'label' => 'Satuan Consumable', 'icon' => 'bi-rulers', 'color' => 'secondary', 'data' => $consumableUnits],
            ];
        @endphp

        @foreach($tables as $t)
        <div class="col-lg-6">
            <div class="card p-3 h-100">
                <h5 class="fw-bold mb-3"><i class="bi {{ $t['icon'] }} me-2 text-{{ $t['color'] }}"></i>{{ $t['label'] }}</h5>
                <input type="text" class="form-control form-control-sm mb-2 master-search" data-target="{{ $t['id'] }}" placeholder="Cari {{ $t['label'] }}...">
                <div class="table-responsive master-table-wrapper">
                    <table class="table table-hover align-middle mb-0" id="table-{{ $t['id'] }}">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th class="text-end" style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($t['data'] as $row)
                            <tr>
                                <td class="master-name">{{ $row->name }}</td>
                                <td class="text-end">
                                    @if(Auth::user()->role === 'admin')
                                    <button class="btn btn-sm btn-outline-danger" onclick="hapusMaster('{{ $t['id'] }}', {{ $row->id }}, '{{ $row->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr class="empty-row"><td colspan="2" class="text-muted text-center py-3">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
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

document.querySelectorAll('.master-search').forEach(function(input) {
    input.addEventListener('keyup', function() {
        var target = this.getAttribute('data-target');
        var q = this.value.toLowerCase().trim();
        var table = document.getElementById('table-' + target);
        var rows = table.querySelectorAll('tbody tr:not(.empty-row)');
        var visible = 0;
        rows.forEach(function(row) {
            var name = row.querySelector('.master-name');
            if (!name) return;
            var match = name.textContent.toLowerCase().includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        // Show/hide empty message
        var empty = table.querySelector('.empty-row');
        if (empty) {
            empty.style.display = rows.length === 0 || visible === 0 ? '' : 'none';
        }
    });
});
</script>
<style>
.master-table-wrapper {
    max-height: 540px;
    overflow-y: auto;
}
.master-table-wrapper::-webkit-scrollbar {
    width: 6px;
}
.master-table-wrapper::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}
</style>
@endpush
@endsection