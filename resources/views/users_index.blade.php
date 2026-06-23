        @extends('layouts.app') @section('content')
        <div class="container">
            <h2 class="fw-bold text-dark mb-4"><i class="bi bi-people me-2"></i>User Management</h2>
            
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalUser">
                + Tambah User Baru
            </button>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Approve</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <span class="badge {{ $u->role == 'admin' ? 'bg-danger' : 'bg-info' }}">
                                {{ strtoupper($u->role) }}
                            </span>
                        </td>
                        <td>
                            @if($u->can_approve)
                                <span class="badge bg-success">Ya</span>
                            @else
                                <span class="badge bg-secondary">Tidak</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUser{{ $u->id }}">
                                Edit
                            </button>

                            @if($u->id != Auth::id())
                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    <div class="modal fade" id="editUser{{ $u->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('users.update', $u->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit User: {{ $u->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Nama</label>
                                            <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Role</label>
                                            <select name="role" class="form-control">
                                                <option value="staff" {{ $u->role == 'staff' ? 'selected' : '' }}>Staff</option>
                                                <option value="admin" {{ $u->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label>Password Baru (Kosongkan jika tidak ganti)</label>
                                            <input type="password" name="password" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="can_approve" value="1" class="form-check-input" id="canApprove{{ $u->id }}" {{ $u->can_approve ? 'checked' : '' }}>
                                                <label class="form-check-label" for="canApprove{{ $u->id }}">Dapat menyetujui permintaan distribusi</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                     @endforeach
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="modalUser" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Tambah User</h5></div>
                        <div class="modal-body">
                            <input type="text" name="name" class="form-control mb-2" placeholder="Nama Lengkap">
                            <input type="email" name="email" class="form-control mb-2" placeholder="Email">
                            <input type="password" name="password" class="form-control mb-2" placeholder="Password">
                            <select name="role" class="form-control">
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="can_approve" value="1" class="form-check-input" id="canApproveNew">
                                <label class="form-check-label" for="canApproveNew">Dapat menyetujui permintaan distribusi</label>
                            </div>
                        </div>
                        <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
                    </div>
                </form>
            </div>
        </div>
        @endsection