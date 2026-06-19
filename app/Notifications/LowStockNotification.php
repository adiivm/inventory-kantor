<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $item
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'current_stock' => $this->item->current_stock,
            'min_stock' => $this->item->min_stock,
            'message' => "Stok barang menipis: {$this->item->name} tersisa {$this->item->current_stock}",
        ];
    }
}
