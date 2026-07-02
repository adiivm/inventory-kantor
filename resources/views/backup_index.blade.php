@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="bi bi-cloud-arrow-up me-2"></i>Backup / Restore</h4>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-download me-2 text-primary"></i>Backup Database</h5>
                <p class="text-muted small mb-3">Membuat backup database + file gambar. Disimpan di folder <code>backups/</code> server (otomatis hapus > 7 hari).</p>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="runBackup()" title="Backup database + file gambar sekarang">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Jalankan Backup
                    </button>
                    <a href="{{ route('backup.download') }}" class="btn btn-outline-primary" title="Download backup langsung ke komputer Anda">
                        <i class="bi bi-download me-2"></i>Download Backup
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-upload me-2 text-warning"></i>Restore Database</h5>
                <p class="text-muted small mb-3">Pilih file <code>.sql</code> dan <code>.tar.gz</code> (gambar) untuk dikembalikan.</p>
                <form id="restoreForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">File Database (.sql)</label>
                        <div class="d-flex gap-2">
                            <select class="form-select" id="restoreFile" required>
                                <option value="">-- Pilih file .sql --</option>
                            </select>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Upload file .sql dari komputer">
                                    <i class="bi bi-folder"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:300px">
                                    <li><input type="file" class="form-control form-control-sm" id="customRestoreFile" accept=".sql"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">File Gambar (.tar.gz) <span class="text-muted fw-normal">(opsional)</span></label>
                        <div class="d-flex gap-2">
                            <select class="form-select" id="restoreImageFile">
                                <option value="">-- Pilih file .tar.gz --</option>
                            </select>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Upload file .tar.gz dari komputer">
                                    <i class="bi bi-folder"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:300px">
                                    <li><input type="file" class="form-control form-control-sm" id="customRestoreImageFile" accept=".tar.gz"></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning px-4" title="Restore database + gambar dari file yang dipilih">
                        <i class="bi bi-upload me-2"></i>Restore
                    </button>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-folder me-2 text-secondary"></i>Daftar Backup Tersimpan</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Tipe</th>
                                <th>Ukuran</th>
                                <th>Tanggal</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="backupFileList">
                            <tr><td colspan="5" class="text-muted text-center py-3">Memuat daftar file...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            <div class="card p-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1 text-danger"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset Data</h5>
                        <p class="text-muted small mb-0">Hapus semua data (produk, transaksi, consumable, supplier, log, master data). <strong class="text-danger">Backup otomatis dijalankan sebelum reset.</strong> User tetap aman.</p>
                    </div>
                    <button class="btn btn-danger px-4 ms-3 flex-shrink-0" onclick="resetData()">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadBackupFiles() {
    fetch('{{ route("backup.files") }}')
        .then(r => r.json())
        .then(files => {
            var tbody = document.getElementById('backupFileList');
            if (!files.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center py-3">Belum ada backup.</td></tr>';
                return;
            }
            tbody.innerHTML = files.map(function(f) {
                var badge = f.type === 'sql' ? 'bg-primary' : 'bg-secondary';
                var size = (f.size / 1024 / 1024).toFixed(2) + ' MB';
                return '<tr>' +
                    '<td><code>' + f.name + '</code></td>' +
                    '<td><span class="badge ' + badge + '">' + f.type.toUpperCase() + '</span></td>' +
                    '<td>' + size + '</td>' +
                    '<td>' + f.date + '</td>' +
                    '<td class="text-end">' +
                        '<button class="btn btn-sm btn-outline-warning me-1" onclick="restoreFile(\'' + f.name + '\')" title="Restore file ini"><i class="bi bi-upload"></i></button>' +
                        '<button class="btn btn-sm btn-outline-danger" onclick="deleteFile(\'' + f.name + '\')" title="Hapus file backup"><i class="bi bi-trash"></i></button>' +
                    '</td>' +
                '</tr>';
            }).join('');

            // Populate select
            // Populate selects
            var sel = document.getElementById('restoreFile');
            sel.innerHTML = '<option value="">-- Pilih file .sql --</option>' +
                files.filter(function(f) { return f.type === 'sql'; }).map(function(f) {
                    return '<option value="' + f.name + '">' + f.name + '</option>';
                }).join('');

            var selImg = document.getElementById('restoreImageFile');
            selImg.innerHTML = '<option value="">-- Pilih file .tar.gz --</option>' +
                files.filter(function(f) { return f.type === 'tar.gz'; }).map(function(f) {
                    return '<option value="' + f.name + '">' + f.name + '</option>';
                }).join('');
        });
}

function runBackup() {
    Swal.fire({
        title: 'Backup Database?',
        text: 'Proses backup akan berjalan. Tunggu beberapa saat.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Backup',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Memproses...', html: 'Sedang backup...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
            fetch('{{ route("backup.run") }}')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', html: data.message.replace(/\n/g, '<br>') });
                        loadBackupFiles();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                    }
                })
                .catch(function(e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: e.message });
                });
        }
    });
}

function restoreFile(name, extraPayload) {
    extraPayload = extraPayload || {};
    Swal.fire({
        title: 'Restore ' + name + '?',
        text: 'Semua data saat ini akan diganti!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        confirmButtonText: 'Ya, Restore',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Merestore...', html: 'Sedang mengembalikan data...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
            var payload = { file: name };
            if (extraPayload.image_file) {
                payload.image_file = extraPayload.image_file;
            }
            fetch('{{ route("backup.restore") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(function(e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.message });
            });
        }
    });
}

function resetData() {
    Swal.fire({
        title: 'Reset Semua Data?',
        text: 'Backup otomatis akan dijalankan SEBELUM reset. Semua data akan dihapus: produk, transaksi, distribusi, consumable, supplier, log, master data (kategori, divisi, dll). User tetap aman.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Backup & Reset',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Memproses...', html: 'Backup otomatis...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
            fetch('{{ route("backup.reset") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', html: data.message.replace(/\n/g, '<br>'), timer: 3000, showConfirmButton: false });
                    loadBackupFiles();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                }
            })
            .catch(function(e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.message });
            });
        }
    });
}

function deleteFile(name) {
    Swal.fire({
        title: 'Hapus ' + name + '?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch('{{ route("backup.delete") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ file: name })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'File dihapus', timer: 1500, showConfirmButton: false });
                loadBackupFiles();
            });
        }
    });
}

document.getElementById('restoreForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var selectFile = document.getElementById('restoreFile').value;
    var selectImageFile = document.getElementById('restoreImageFile').value;
    var customFile = document.getElementById('customRestoreFile').files[0];
    var customImageFile = document.getElementById('customRestoreImageFile').files[0];

    if (!selectFile && !customFile) {
        Swal.fire({ icon: 'warning', title: 'Pilih file', text: 'Pilih file .sql dari daftar atau upload file sendiri.' });
        return;
    }

    if (customFile) {
        var formData = new FormData();
        formData.append('file', customFile);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        Swal.fire({ title: 'Mengupload...', html: 'Sedang mengupload dan merestore...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

        fetch('{{ route("backup.restore.upload") }}', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 2000, showConfirmButton: false });
                loadBackupFiles();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
            }
        })
        .catch(function(e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message });
        });
        return;
    }

    // Restore from selected files
    var payload = { file: selectFile };
    if (selectImageFile) {
        payload.image_file = selectImageFile;
    }
    restoreFile(selectFile, payload);
});

loadBackupFiles();

document.addEventListener('DOMContentLoaded', function() {
    var tooltips = [].slice.call(document.querySelectorAll('[title]'));
    tooltips.map(function(el) { return new bootstrap.Tooltip(el); });
});
</script>
@endpush
