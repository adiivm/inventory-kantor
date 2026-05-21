<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>if(typeof Swal==='undefined'){window.Swal={fire:alert,mixin:()=>({fire:alert})}}</script>
    
    <style>
        /* --- Tambahan untuk Fitur Hide/Toggle --- */

        /* Efek transisi untuk konten agar halus saat sidebar geser */
        #content {
            transition: all 0.3s ease;
        }

        /* Kondisi saat Sidebar disembunyikan di Desktop (PC) */
        body.sidebar-toggled #sidebar {
            left: calc(-1 * var(--sidebar-width));
        }

        body.sidebar-toggled #content {
            margin-left: 0;
            width: 100%;
        }

        /* Overlay untuk Mobile: Biar kalau klik di luar menu, menu otomatis tutup */
        #sidebar-overlay {
            display: none;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.3);
            z-index: 999; /* di bawah sidebar (1000), di atas content */
            top: 0;
            left: 0;
        }

        @media (max-width: 992px) {
            #sidebar.active + #sidebar-overlay {
                display: block;
            }
        }
        .modal.fade .modal-dialog {
            transform: scale(0.9);
            transition: transform 0.8s ease-out;
        }
        .modal.show .modal-dialog {
            transform: scale(1);
        }
        
        :root {
            --sidebar-width: 260px;
            --primary-color: #4361ee;
            --soft-bg: #fdfdfe;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f4f7fe; 
            color: #2b3674;
            overflow-x: hidden;
        }

        /* --- SIDEBAR STYLE --- */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #fff;
            transition: all 0.3s;
            z-index: 1000;
            border-right: 1px solid #e9edf7;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
        }

        .nav-pills .nav-link {
            color: #a3adc2;
            padding: 12px 25px;
            border-radius: 0;
            font-weight: 500;
            transition: 0.3s;
            border-right: 4px solid transparent;
        }

        .nav-pills .nav-link:hover {
            background: #f4f7fe;
            color: var(--primary-color);
        }

        .nav-pills .nav-link.active {
            background: #f4f7fe;
            color: var(--primary-color);
            border-right: 4px solid var(--primary-color);
        }

        .nav-pills .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        /* --- MAIN CONTENT STYLE --- */
        #content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s;
            padding: 20px;
        }

        .top-navbar {
            background: #fff;
            padding: 15px 30px;
            border-radius: 15px;
            box-shadow: 14px 17px 40px 4px rgba(112, 144, 176, 0.08);
            margin-bottom: 30px;
        }

        .card { 
            border: none;
            border-radius: 20px; 
            box-shadow: 14px 17px 40px 4px rgba(112, 144, 176, 0.08);
            padding: 10px;
        }

        /* --- UI COMPONENTS --- */
        .btn-primary { background-color: var(--primary-color); border: none; padding: 10px 20px; border-radius: 12px; }
        .btn-primary:hover { background-color: #3651d1; }
        
        .table thead th {
            background-color: transparent;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #a3adc2;
            border-bottom: 1px solid #e9edf7;
        }

        /* --- RESPONSIVE SIDEBAR --- */
        @media (max-width: 992px) {
            #sidebar { left: calc(-1 * var(--sidebar-width)); }
            #sidebar.active { left: 0; }
            #content { margin-left: 0; width: 100%; }
        }

        /* Styling Mobile Card */
        @media (max-width: 768px) {
            thead { display: none; }
            tr { display: block; margin-bottom: 1.5rem; border: 1px solid #e9edf7 !important; border-radius: 15px; background: #fff; padding: 10px; }
            td { display: flex; justify-content: space-between; border: none !important; padding: 8px 5px !important; }
            td::before { content: attr(data-label); font-weight: 700; color: #a3adc2; font-size: 0.7rem; text-transform: uppercase; }
            
            /* Mobile tweaks */
            .top-navbar { padding: 10px 15px !important; margin-bottom: 15px !important; }
            .top-navbar h2 { font-size: 1rem !important; }
            .card { padding: 15px !important; border-radius: 15px !important; }
            .btn { padding: 8px 12px !important; font-size: 0.875rem; }
            .form-control, .form-select { font-size: 0.875rem; padding: 8px 12px; }
            .table { font-size: 0.875rem; }
            .modal-dialog { margin: 10px !important; }
            .modal-body { padding: 15px !important; }
            h1, h2, h3, h4, h5, h6 { font-size: 1.1rem; }
            .navbar-brand img { width: 30px !important; }
            .dropdown-menu { width: 100% !important; }
            .nav-link { padding: 10px 15px !important; font-size: 0.9rem; }
        }

        /* Extra small screens */
        @media (max-width: 576px) {
            .card { padding: 10px !important; }
            .btn-sm { padding: 5px 10px; font-size: 0.75rem; }
            .form-label { font-size: 0.85rem; }
            .badge { font-size: 0.7rem; }
        }

        /* Action buttons mobile fix */
        .action-buttons .btn { padding: 4px 8px; min-width: 32px; }
        @media (max-width: 576px) {
            .action-buttons { justify-content: flex-start !important; }
            .action-buttons .btn { padding: 3px 6px; }
        }

 
    </style>
</head>
<body>
    <nav id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('images/ivans_motor.png') }}" alt="Logo" class="img-fluid mb-2" style="filter: brightness(0) saturate(100%) invert(34%) sepia(87%) animate(204%) hue-rotate(222deg) brightness(97%) contrast(92%);">
            <h6 class="fw-bold mt-2">Inventory System</h6>
            <hr class="text-muted">
        </div>

        <div class="nav flex-column nav-pills">
            <a href="/dashboard" class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="/products" class="nav-link {{ request()->is('products*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Assets
            </a>
            
            <a href="{{ route('product.trash') }}" class="nav-link {{ request()->is('trash*') ? 'active' : '' }}">
                <i class="bi bi-archive"></i> Archive
            </a>

            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->is('report*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Reports
            </a>

            <a href="{{ route('profile.index') }}" class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
            
            @if(Auth::check() && Auth::user()->role === 'admin')
            <a href="{{ route('users.index') }}" class="nav-link">
                <i class="bi bi-people"></i> User Management
            </a>
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> Suppliers
            </a>
            @endif

            <div class="mt-auto p-3">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light w-100 text-danger fw-bold rounded-3">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <div id="sidebar-overlay"></div>
    <div id="content">
        <nav class="top-navbar d-flex justify-content-between align-items-center">
            <button type="button" id="sidebarCollapse" class="btn btn-light">
                <i class="bi bi-list"></i>
            </button>
            
            <h2 class="mb-0 fw-bold d-none d-sm-block text-muted">Selamat Datang {{ Auth::user()->name }}👋</h2>

            <div class="user-info d-flex align-items-center gap-2">
                <!-- Notifikasi Garansi Kritis -->
                @if($jmlGaransiKritis > 0)
                <div class="dropdown me-2">
                    <a href="#" class="position-relative text-dark d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill text-warning" style="font-size: 1.1rem;"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem; min-width: 16px; height: 16px;">
                            {{ $jmlGaransiKritis }}
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2" style="width: 280px; max-width: 90vw; border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center px-2 py-2 border-bottom">
                            <span class="fw-bold text-warning small"><i class="bi bi-exclamation-triangle me-1"></i> Garansi Kritis</span>
                            <a href="{{ route('product.index', ['warranty_status' => 'critical']) }}" class="btn btn-sm btn-warning py-1 px-2" style="font-size: 0.7rem;">Lihat</a>
                        </div>
                        @foreach($garansiKritis as $item)
                        @php
                            $expiryDate = \Carbon\Carbon::parse($item->warranty_expiry_date)->startOfDay();
                            $currentDay = now()->startOfDay();
                            $daysDiff = $currentDay->diffInDays($expiryDate, false);
                        @endphp
                        <a href="{{ route('product.index', ['search_sku' => $item->sku]) }}" class="dropdown-item py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="d-block" style="font-size: 0.8rem;">{{ $item->sku }}</strong>
                                    <span class="text-muted" style="font-size: 0.7rem;">{{ Str::limit($item->name, 15) }}</span>
                                </div>
                                <div class="text-end">
                                    @if($daysDiff > 0)
                                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">{{ $daysDiff }} hr</span>
                                    @elseif($daysDiff === 0)
                                        <span class="badge bg-danger text-white" style="font-size: 0.65rem;">Habis!</span>
                                    @else
                                        <span class="badge bg-secondary text-white" style="font-size: 0.65rem;">+{{ abs($daysDiff) }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="text-end me-2 d-none d-md-block">
                    <p class="mb-0 fw-bold" style="font-size: 0.85rem;">{{ Auth::user()->name }}</p>
                    <small class="text-muted" style="font-size: 0.7rem;">{{ strtoupper(Auth::user()->role) }}</small>
                </div>
                
                <!-- Bungkus foto dengan dropdown -->
                <div class="dropdown">
                    @if(Auth::user()->photo)
                        <img src="{{ asset('storage/photos/' . Auth::user()->photo) }}" 
                             class="rounded-circle shadow-sm dropdown-toggle" 
                             width="36" height="36" 
                             style="object-fit: cover; border: 2px solid var(--primary-color); cursor: pointer;" 
                             id="userDropdown" 
                             data-bs-toggle="dropdown" 
                             aria-expanded="false">
                    @else
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm dropdown-toggle" 
                             style="width: 36px; height: 36px; cursor: pointer; font-size: 0.9rem;" 
                             id="userDropdown" 
                             data-bs-toggle="dropdown" 
                             aria-expanded="false">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                    
                    <!-- Menu Dropdown -->
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="userDropdown" style="border-radius: 15px;">
                        <li>
                            <a class="dropdown-item py-2 px-3" href="{{ route('profile.index') }}">
                                <i class="bi bi-person-circle me-2"></i> My Profile
                            </a>
                        </li>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <li>
                                <a class="dropdown-item py-2 px-3" href="{{ route('users.index') }}">
                                    <i class="bi bi-people me-2"></i> User Management
                                </a>
                            </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 px-3 text-danger fw-bold border-0 bg-transparent w-100 text-start">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div id="print-buffer" class="d-none d-print-block"></div>

        <div class="container-fluid animate__animated animate__fadeIn">
            @yield('content')
        </div>
    </div>
    <div class="modal fade" id="modalLog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title px-3">📜 Riwayat Barang: <span id="logProductName" class="fw-bold"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                    <th>Keterangan</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody id="logTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHeld_by" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title ps-2"><i class="bi bi-person-plus-fill me-2"></i>Tambah Pemegang Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeHeld_by"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold text-muted">Nama Pemegang</label>
                    <input type="text" id="new_held_by_name" class="form-control form-control-lg border-2" 
                        placeholder="Masukkan nama personil..." style="border-radius: 10px;">
                    <small class="text-muted mt-2 d-block px-1">Nama ini akan muncul di daftar pemegang barang.</small>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" 
                            onclick="saveHeld_by()" style="border-radius: 10px;">
                        <i class="bi bi-plus-lg me-1"></i> Simpan Pemegang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLocation" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title ps-2"><i class="bi bi-geo-alt-fill me-2"></i>Add New Location</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeLocation"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold text-muted">Nama Lokasi</label>
                    <input type="text" id="new_location_name" class="form-control form-control-lg border-2" 
                        placeholder="Contoh: Lantai 2, Gudang A..." style="border-radius: 10px;">
                    <small class="text-muted mt-2 d-block px-1">Pastikan lokasi spesifik agar mudah dicari.</small>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" 
                            onclick="saveLocation()" style="border-radius: 10px;">
                        <i class="bi bi-plus-lg me-1"></i> Save Location
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSupplier" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title ps-2"><i class="bi bi-truck me-2"></i>Add New Supplier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeSupplier"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Nama Supplier</label>
                        <input type="text" id="new_supplier_name" class="form-control form-control-lg border-2" 
                            placeholder="Contoh: PT Sumber Jaya" style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Contact Person</label>
                        <input type="text" id="new_supplier_contact" class="form-control border-2" 
                            placeholder="Nama kontak" style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Telepon</label>
                        <input type="text" id="new_supplier_phone" class="form-control border-2" 
                            placeholder="Nomor telepon" style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Email</label>
                        <input type="email" id="new_supplier_email" class="form-control border-2" 
                            placeholder="email@supplier.com" style="border-radius: 10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted">Alamat</label>
                        <textarea id="new_supplier_address" class="form-control border-2" 
                            placeholder="Alamat lengkap" rows="2" style="border-radius: 10px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" 
                            onclick="saveSupplier()" style="border-radius: 10px;">
                        <i class="bi bi-plus-lg me-1"></i> Save Supplier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title ps-2"><i class="bi bi-tag-fill me-2"></i>Add New Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeKategori"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold text-muted">Nama Kategori</label>
                    <input type="text" id="new_category_name" class="form-control form-control-lg border-2" 
                        placeholder="Contoh: Elektronik, Alat Tulis..." style="border-radius: 10px;">
                    <small class="text-muted mt-2 d-block px-1">Pastikan nama kategori belum terdaftar sebelumnya.</small>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" 
                            onclick="saveCategory()" style="border-radius: 10px;">
                        <i class="bi bi-plus-lg me-1"></i> Simpan Kategori
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDivisi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title ps-2"><i class="bi bi-building me-2"></i>Tambah Divisi Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeDivisi"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold text-muted">Nama Divisi</label>
                    <input type="text" id="new_division_name" class="form-control form-control-lg border-2" 
                        placeholder="Contoh: IT, Marketing, HRD..." style="border-radius: 10px;">
                    <small class="text-muted mt-2 d-block px-1">Gunakan nama divisi resmi kantor.</small>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" 
                            onclick="saveDivisions()" style="border-radius: 10px;">
                        <i class="bi bi-plus-lg me-1"></i> Simpan Divisi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Server IP untuk QR Code - gunakan IP server yang bisa diakses dari HP
        const serverHost = "{{ config('app.url', 'http://172.17.7.70:8080') }}".replace(/https?:\/\//, '');
    </script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Toggle Sidebar untuk PC dan Mobile
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const btnToggle = document.getElementById('sidebarCollapse');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function toggleSidebar() {
            if (window.innerWidth > 992) {
                // Logika untuk PC: Sembunyikan total
                document.body.classList.toggle('sidebar-toggled');
            } else {
                // Logika untuk HP: Munculkan menu slide-in
                sidebar.classList.toggle('active');
            }
        }

        // Klik tombol hamburger
        btnToggle.addEventListener('click', toggleSidebar);

        // Klik overlay (area gelap) untuk menutup menu di HP
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });

        

        /** --- LOG HISTORY LOGIC --- **/
        function showLogs(productId) {
            const body = document.getElementById('logTableBody');
            body.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Memuat data...</td></tr>';
            
            new bootstrap.Modal(document.getElementById('modalLog')).show();

            fetch(`/product/${productId}/logs`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('logProductName').innerText = data.product_name;
                    let rows = '';
                    
                    if(data.logs.length > 0) {
                        data.logs.forEach(log => {
                            let date = new Date(log.created_at).toLocaleString('id-ID');
                            let actionBadge = {
                                'CREATE': 'bg-success',
                                'UPDATE': 'bg-primary',
                                'DELETE': 'bg-danger'
                            }[log.action] || 'bg-secondary';

                            let badgeClass = log.description.includes('+') ? 'text-success fw-bold' : 
                                        (log.description.includes('-') ? 'text-danger fw-bold' : '');

                            rows += `
                                <tr>
                                    <td><small class="text-muted">${date}</small></td>
                                    <td><span class="badge ${actionBadge} rounded-pill px-3">${log.action}</span></td>
                                    <td class="${badgeClass}">${escapeHtml(log.description)}</td>
                                    <td><span class="fw-medium">${log.user_name ?? 'System'}</span></td>
                                </tr>`;
                        });
                    } else {
                        rows = '<tr><td colspan="4" class="text-center py-4">Belum ada riwayat.</td></tr>';
                    }
                    body.innerHTML = rows;
                })
                .catch(() => body.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Gagal memuat.</td></tr>');
        }

        /** --- PRINTING LOGIC --- **/
        function printLabel(sku, name) {
            const auditLink = `http://${serverHost}/audit/direct/${sku}`;
            const printWindow = window.open('', '_blank', 'width=500,height=400');
            
            printWindow.document.write(`
                <html>
                    <head>
                        <style>
                            /* --- 1. SETTING UKURAN KERTAS (LEBIH BESAR) --- */
                            @page {
                                size: 70mm 35mm; 
                                margin: 0;
                            }

                            /* --- 2. LAYOUT KIRI-KANAN (FLEX ROW) --- */
                            body { 
                                font-family: 'Inter', sans-serif, Arial; 
                                margin: 0; 
                                padding: 2mm 3mm;
                                background-color: #fff;
                                color: #000;
                                display: flex;
                                flex-direction: row;
                                align-items: center;
                                height: 35mm;
                                width: 70mm;
                                box-sizing: border-box;
                            }

                            /* --- 3. WADAH QR CODE & TEKS --- */
                            #qrcode { 
                                margin-right: 4mm;
                                flex-shrink: 0;
                            }
                            
                            .text-content {
                                display: flex;
                                flex-direction: column;
                                justify-content: center;
                                overflow: hidden;
                                width: 100%;
                            }

                            /* --- 4. UKURAN FONT (LEBIH BESAR) --- */
                            p.sku { 
                                font-size: 14px; 
                                margin: 0 0 2px 0; 
                            }

                            h3.name { 
                                font-size: 12px;
                                margin: 0; 
                                white-space: nowrap; 
                                overflow: hidden; 
                                text-overflow: ellipsis;
                                font-weight: normal;
                            }
                        </style>
                    </head>
                    <body>
                        <div id="qrcode"></div>
                        
                        <div class="text-content">
                            <p class="sku"><strong>${sku}</strong></p>
                            <h3 class="name">${name}</h3>
                        </div>
                        
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
                        <script>
                            // --- 5. QR CODE LEBIH BESAR UNTUK 35MM ---
                            new QRCode(document.getElementById("qrcode"), { 
                                text: "${auditLink}", 
                                width: 80,  // Diperbesar untuk label 35mm
                                height: 80 
                            });
                            
                            setTimeout(() => { 
                                window.print(); 
                                window.close(); 
                            }, 800);
                        <\/script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        }

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        /** --- AJAX SAVE LOGIC (Reusable) --- **/
        async function handleAjaxSave(apiUrl, inputId, selectId, closeBtnId, entityName) {
            const input = document.getElementById(inputId);
            const name = input.value.trim();
            
            if(!name) return Swal.fire('Oops!', `Nama ${entityName} tidak boleh kosong.`, 'warning');

            try {
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken 
                    },
                    body: JSON.stringify({ name: name })
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.errors?.name?.[0] || 'Gagal menyimpan');

                const select = document.getElementById(selectId);
                select.add(new Option(data.name, data.id));
                select.value = data.id; 

                document.getElementById(closeBtnId).click();
                input.value = '';
                Toast.fire({ icon: 'success', title: `${entityName} berhasil ditambahkan` });

            } catch (err) {
                Swal.fire('Error', err.message, 'error');
            }
        }

        // Trigger functions
        const saveCategory = () => handleAjaxSave('/api/categories', 'new_category_name', 'category_select', 'closeKategori', 'Kategori');
        const saveDivisions = () => handleAjaxSave('/api/divisions', 'new_division_name', 'division_select', 'closeDivisi', 'Divisi');
        const saveHeld_by = () => handleAjaxSave('/api/held_bies', 'new_held_by_name', 'held_by_select', 'closeHeld_by', 'Pemegang');
        const saveLocation = () => handleAjaxSave('/api/locations', 'new_location_name', 'location_select', 'closeLocation', 'Lokasi');

        const saveSupplier = () => {
            const name = document.getElementById('new_supplier_name').value.trim();
            if(!name) return Swal.fire('Oops!', 'Nama Supplier tidak boleh kosong.', 'warning');

            const formData = new FormData();
            formData.append('name', name);
            formData.append('contact_person', document.getElementById('new_supplier_contact').value.trim());
            formData.append('phone', document.getElementById('new_supplier_phone').value.trim());
            formData.append('email', document.getElementById('new_supplier_email').value.trim());
            formData.append('address', document.getElementById('new_supplier_address').value.trim());
            formData.append('_token', csrfToken);

            fetch('/suppliers', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('closeSupplier').click();
                    Toast.fire({ icon: 'success', title: 'Supplier berhasil ditambahkan' });
                    
                    // Add to dropdown and select
                    const select = document.getElementById('supplier_select');
                    if(select) {
                        const option = document.createElement('option');
                        option.value = data.data.id;
                        option.text = data.data.name;
                        option.selected = true;
                        select.add(option);
                    }
                    
                    // Reset form fields
                    document.getElementById('new_supplier_name').value = '';
                    document.getElementById('new_supplier_contact').value = '';
                    document.getElementById('new_supplier_phone').value = '';
                    document.getElementById('new_supplier_email').value = '';
                    document.getElementById('new_supplier_address').value = '';
                }
            })
            .catch(e => {
                const errorMsg = e.response?.data?.message || e.message || 'Terjadi kesalahan';
                Swal.fire('Error', errorMsg, 'error');
            });
        };

        // Fungsi untuk membuka Modal Gambar di Tabel
        function showPreview(id) {
            const modalElement = document.getElementById('modalGambar' + id);
            if (modalElement) {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            } else {
                console.error("Modal dengan ID modalGambar" + id + " tidak ditemukan!");
            }
        }

        /** --- UI HELPERS --- **/
        function previewImage(input) {
            const container = document.getElementById('preview-container'); // Pastikan ID ini ada di HTML
            container.innerHTML = ''; // Bersihkan preview lama

            if (input.files) {
                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        // Buat elemen div untuk menampung gambar
                        const div = document.createElement('div');
                        div.className = 'position-relative d-inline-block m-1';
                        div.innerHTML = `
                            <img src="${e.target.result}" 
                                class="img-thumbnail" 
                                style="width: 80px; height: 80px; object-fit: cover;">
                        `;
                        container.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.unit-input').forEach(i => total += (parseInt(i.value) || 0));
            document.getElementById('total_stock').value = total;
        }
    </script>
    <script>
        const inputHarga = document.getElementById('inputHarga');
        const priceReal = document.getElementById('priceReal');

        function formatRupiah(value) {
            if (!value) return '';
            let number_string = value.replace(/[^,\d]/g, '').toString();
            let split = number_string.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        }

        // Listener Keyup - Aman dengan IF
        if (inputHarga && priceReal) {
            inputHarga.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
                // Buat jadi angka murni
                priceReal.value = this.value.replace(/\./g, ''); 
            });
        }

        // Bagian ini yang tadi bikin error, sekarang sudah aman dengan IF
        window.addEventListener('DOMContentLoaded', (event) => {
            if (inputHarga && priceReal && inputHarga.value) {
                priceReal.value = inputHarga.value.replace(/\./g, '');
            }
        });
    </script>
    <script>
        // Stock is now always 1, condition is selected via radio buttons
    </script>
    <script>
        // 1. Fungsi Ganti Foto Utama (Bintang) - SUDAH OKE
        function setPrimary(imageId) {
            Swal.fire({
                title: 'Jadikan Utama?',
                text: "Foto ini akan muncul sebagai sampul produk.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Jadikan Utama!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/product/image-primary') }}/" + imageId,
                        type: "POST",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            Toast.fire({ icon: 'success', title: 'Foto utama diperbarui' });
                            setTimeout(() => { location.reload(); }, 1000);
                        },
                        error: function() {
                            Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        }

        // 2. Fungsi Hapus Foto (Satu Fungsi untuk Semua)
        function confirmDeleteImage(imageId) {
            Swal.fire({
                title: 'Yakin mau hapus?',
                text: "Foto yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dd4b39',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus Saja!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        // PASTIKAN URL INI SAMA DENGAN ROUTE DI WEB.PHP
                        url: "{{ url('/product/image-delete') }}/" + imageId, 
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            Toast.fire({ icon: 'success', title: 'Foto terhapus' });
                            
                            // Menghapus elemen dari UI (Gunakan ID yang sesuai di HTML-mu)
                            $(`.group-image-${imageId}`).fadeOut(500); 
                            $(`#old-image-${imageId}`).fadeOut(500);
                        },
                        error: function() {
                            Swal.fire('Gagal!', 'Gagal menghapus foto.', 'error');
                        }
                    });
                }
            });
        }

        // 3. Logic Upload & Preview (Tetap Biarkan)
        let fileCount = 0;
        function triggerUpload() {
            fileCount++;
            const inputId = `file-input-${fileCount}`;
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'images[]';
            input.id = inputId;
            input.accept = 'image/*';
            input.multiple = true;
            input.style.display = 'none';

            input.onchange = function(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    document.getElementById('gallery-inputs').appendChild(input);
                    Array.from(files).forEach((file, index) => {
                        const reader = new FileReader();
                        const previewId = `prev-${fileCount}-${index}`;
                        reader.onload = function(e) {
                            const html = `
                                <div class="col-md-3 position-relative mb-3" id="${previewId}">
                                    <img src="${e.target.result}" class="img-thumbnail w-100" style="height: 120px; object-fit: cover; border-radius:10px;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                        onclick="removeNewPreview('${previewId}', '${inputId}')">✕</button>
                                    <small class="badge bg-success position-absolute bottom-0 start-0 m-1">Baru</small>
                                </div>`;
                            document.getElementById('gallery-preview').insertAdjacentHTML('beforeend', html);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            };
            input.click();
        }

        function removeNewPreview(previewId, inputId) {
            $(`#${previewId}`).remove();
        }

        function getConditionBadge(cond) {
            cond = (cond || 'ready').toLowerCase();
            const badges = {
                'ready': '<span class="badge bg-success fs-6">Ready</span>',
                'repair': '<span class="badge bg-warning text-dark fs-6">Servis</span>',
                'broken': '<span class="badge bg-danger fs-6">Rusak</span>',
                'disposed': '<span class="badge bg-secondary fs-6">Dibuang</span>'
            };
            return badges[cond] || badges['ready'];
        }


        // Setup global untuk JQuery (agar AJAX $.ajax juga aman)
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    </script>


    @stack('scripts')
    <div id="printSection" class="d-none"></div>
</body>
</html>