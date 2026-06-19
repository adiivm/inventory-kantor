<nav id="sidebar" class="d-flex flex-column flex-shrink-0 bg-white" style="width: 250px; min-width: 250px; max-width: 100%;">

    <div class="sidebar-header p-3 text-center">
        <img src="{{ asset('images/ivans_motor.png') }}" alt="Logo" class="img-fluid mb-2" style="max-height: 50px;">
        <h6 class="fw-bold mt-2">Inventory System</h6>
        <hr class="text-muted">
    </div>

    <div class="sidebar-menu">

        {{-- ASSET MANAGEMENT --}}
        @php
            $assetActive = request()->is('dashboard*') || request()->is('products*') || request()->is('product*') || request()->is('reports*');
        @endphp
        <div class="sidebar-group">
            <div class="sidebar-group-title" data-bs-toggle="collapse" data-bs-target="#collapseAsset" role="button" aria-expanded="{{ $assetActive ? 'true' : 'false' }}">
                <i class="bi bi-chevron-down sidebar-chevron"></i> Asset Management
            </div>
            <div class="collapse {{ $assetActive ? 'show' : '' }}" id="collapseAsset">
                <a href="/dashboard" class="sidebar-link {{ request()->is('dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                </a>
                <a href="/products" class="sidebar-link {{ request()->is('products*') || request()->is('product*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> <span>Assets</span>
                </a>
                <a href="{{ route('product.trash') }}" class="sidebar-link {{ request()->is('product/trash*') || request()->is('trash*') ? 'active' : '' }}">
                    <i class="bi bi-archive"></i> <span>Archive</span>
                </a>
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->is('reports*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> <span>Laporan Asset</span>
                </a>
            </div>
        </div>

        {{-- CONSUMABLE INVENTORY --}}
        @php
            $consumableActive = request()->is('consumable/items*') || request()->is('consumable/transactions*') || request()->is('consumable/distributions*') || request()->is('consumable/reports*') || request()->is('consumable/dashboard*');
        @endphp
        <div class="sidebar-group">
            <div class="sidebar-group-title" data-bs-toggle="collapse" data-bs-target="#collapseConsumable" role="button" aria-expanded="{{ $consumableActive ? 'true' : 'false' }}">
                <i class="bi bi-chevron-down sidebar-chevron"></i> Consumable Inventory
            </div>
            <div class="collapse {{ $consumableActive ? 'show' : '' }}" id="collapseConsumable">
                <a href="{{ route('consumable.dashboard') }}" class="sidebar-link {{ request()->is('consumable/dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                </a>
                <a href="/consumable/items" class="sidebar-link {{ request()->is('consumable/items*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i> <span>Consumable</span>
                </a>
                <a href="/consumable/transactions" class="sidebar-link {{ request()->is('consumable/transactions*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i> <span>Transaksi Masuk</span>
                </a>
                <a href="/consumable/distributions" class="sidebar-link {{ request()->is('consumable/distributions*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i> <span>Distribusi</span>
                </a>
                <a href="/consumable/reports" class="sidebar-link {{ request()->is('consumable/reports*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> <span>Laporan Consumable</span>
                </a>
            </div>
        </div>

        {{-- MASTER --}}
        @php
            $masterActive = request()->is('master-data*') || request()->is('suppliers*') || request()->is('users*') || request()->is('profile*') || request()->is('consumable/categories*') || request()->is('consumable/units*');
        @endphp
        <div class="sidebar-group">
            <div class="sidebar-group-title" data-bs-toggle="collapse" data-bs-target="#collapseMaster" role="button" aria-expanded="{{ $masterActive ? 'true' : 'false' }}">
                <i class="bi bi-chevron-down sidebar-chevron"></i> Master
            </div>
            <div class="collapse {{ $masterActive ? 'show' : '' }}" id="collapseMaster">
                <a href="/master-data" class="sidebar-link {{ request()->is('master-data*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> <span>Master Data</span>
                </a>
                <a href="{{ route('suppliers.index') }}" class="sidebar-link {{ request()->is('suppliers*') ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill"></i> <span>Master Supplier</span>
                </a>

                @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->is('users*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> <span>User Management</span>
                </a>
                @endif
                <a href="{{ route('profile.index') }}" class="sidebar-link {{ request()->is('profile*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> <span>My Profile</span>
                </a>
            </div>
        </div>
    </div>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-light w-100 text-danger fw-bold rounded-3">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</nav>
