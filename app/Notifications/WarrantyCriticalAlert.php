<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WarrantyCriticalAlert extends Notification
{
    use Queueable;

    public array $products;

    public function __construct(array $products)
    {
        $this->products = $products;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'products' => $this->products,
            'total' => count($this->products),
            'message' => count($this->products) . ' produk dengan garansi kritis/expired',
        ];
    }
}
