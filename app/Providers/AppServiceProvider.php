<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Models\Product;
use App\Models\DistributionHeader;
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

        // View Composer untuk data notifikasi (Navbar)
        View::composer('layouts.app', function ($view) {
            $garansiKritis = Product::active()->warrantyCritical()
                ->orderBy('warranty_expiry_date', 'asc')
                ->limit(5)
                ->get();

            $jmlGaransiKritis = Product::active()->warrantyCritical()->count();

            $distribusiNotifications = collect();
            $jmlDistribusi = 0;

            $stockInNotifications = collect();
            $jmlStockIn = 0;

            if (auth()->check()) {
                $rawNotifications = auth()->user()->unreadNotifications()
                    ->where('type', 'App\Notifications\DistributionPendingNotification')
                    ->limit(10)
                    ->get();

                // Hanya tampilkan notifikasi untuk distribusi yang masih pending
                $distribusiNotifications = $rawNotifications->filter(function ($notif) {
                    $distId = $notif->data['distribution_id'] ?? null;
                    if (!$distId) return false;
                    $header = DistributionHeader::find($distId);
                    return $header && $header->status === 'pending';
                })->take(5);

                $jmlDistribusi = $distribusiNotifications->count();

                $stockInNotifications = auth()->user()->unreadNotifications()
                    ->where('type', 'App\Notifications\StockInPendingNotification')
                    ->limit(10)
                    ->get()
                    ->filter(function ($notif) {
                        $txId = $notif->data['stock_transaction_id'] ?? null;
                        if (!$txId) return false;
                        $tx = \App\Models\StockTransaction::find($txId);
                        return $tx && $tx->status === 'pending';
                    })->take(5);
                $jmlStockIn = $stockInNotifications->count();
            }

            $view->with('garansiKritis', $garansiKritis)
                 ->with('jmlGaransiKritis', $jmlGaransiKritis)
                 ->with('distribusiNotifications', $distribusiNotifications)
                 ->with('jmlDistribusi', $jmlDistribusi)
                 ->with('stockInNotifications', $stockInNotifications)
                 ->with('jmlStockIn', $jmlStockIn);
        });
    }
}
