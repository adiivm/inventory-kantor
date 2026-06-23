<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Models\Product;
use App\Models\DistributionHeader;
use App\Models\StockTransaction;
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

            if (auth()->check() && auth()->user()->can_approve) {
                $distribusiNotifications = DistributionHeader::where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($d) {
                        return (object) [
                            'data' => [
                                'distribution_id' => $d->id,
                                'reference_number' => $d->reference_number,
                                'requester_name' => $d->requester_name,
                            ],
                            'created_at' => $d->created_at,
                        ];
                    });

                $jmlDistribusi = DistributionHeader::where('status', 'pending')->count();

                $stockInNotifications = StockTransaction::where('status', 'pending')
                    ->whereIn('type', ['in', 'adjustment'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($tx) {
                        return (object) [
                            'data' => [
                                'stock_transaction_id' => $tx->id,
                                'item_name' => $tx->consumableItem->name,
                                'qty' => $tx->qty,
                                'type' => $tx->type,
                            ],
                            'created_at' => $tx->created_at,
                        ];
                    });

                $jmlStockIn = StockTransaction::where('status', 'pending')->whereIn('type', ['in', 'adjustment'])->count();
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
