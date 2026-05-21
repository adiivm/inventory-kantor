<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Models\Product;
use App\Observers\ProductObserver;

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
        Product::observe(ProductObserver::class);

        // Aturan 1: Hanya Admin yang boleh mengelola user dan hapus barang
        Gate::define('admin-only', function (User $user) {
            return $user->role === 'admin';
        });

        // Aturan 2: Staff hanya boleh melihat dan menambah (opsional)
        Gate::define('staff-access', function (User $user) {
            return $user->role === 'admin' || $user->role === 'staff';
        });

        // View Composer untuk data garansi kritis + notifikasi (Navbar)
        View::composer('layouts.app', function ($view) {
            $garansiKritis = Product::active()->warrantyCritical()
                ->orderBy('warranty_expiry_date', 'asc')
                ->limit(5)
                ->get();

            $jmlGaransiKritis = Product::active()->warrantyCritical()->count();

            $unreadNotifications = collect();
            $jmlNotifikasi = 0;

            if (auth()->check()) {
                $unreadNotifications = auth()->user()->unreadNotifications()->limit(5)->get();
                $jmlNotifikasi = auth()->user()->unreadNotifications()->count();
            }

            $view->with('garansiKritis', $garansiKritis)
                 ->with('jmlGaransiKritis', $jmlGaransiKritis)
                 ->with('unreadNotifications', $unreadNotifications)
                 ->with('jmlNotifikasi', $jmlNotifikasi);
        });
    }
}
