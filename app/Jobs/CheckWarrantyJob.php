<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use App\Models\Product;
use App\Models\User;
use App\Notifications\WarrantyCriticalAlert;

class CheckWarrantyJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $critical = Product::active()->warrantyCritical()
            ->with(['category', 'division'])
            ->get()
            ->map(fn($p) => [
                'sku' => $p->sku,
                'name' => $p->name,
                'days_left' => now()->diffInDays($p->warranty_expiry_date),
                'expiry' => $p->warranty_expiry_date->format('d/m/Y'),
            ])
            ->toArray();

        $expired = Product::active()->warrantyExpired()
            ->with(['category', 'division'])
            ->get()
            ->map(fn($p) => [
                'sku' => $p->sku,
                'name' => $p->name,
                'days_left' => 0,
                'expiry' => $p->warranty_expiry_date->format('d/m/Y'),
            ])
            ->toArray();

        $all = array_merge($critical, $expired);

        if (empty($all)) {
            return;
        }

        $admins = User::where('role', 'admin')->get();

        Notification::send($admins, new WarrantyCriticalAlert($all));
    }
}
