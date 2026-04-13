<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Import di bagian paling atas file
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aturan 1: Hanya Admin yang boleh mengelola user dan hapus barang
        Gate::define('admin-only', function (User $user) {
            return $user->role === 'admin';
        });

        // Aturan 2: Staff hanya boleh melihat dan menambah (opsional)
        Gate::define('staff-access', function (User $user) {
            return $user->role === 'admin' || $user->role === 'staff';
        });
    }
}
