<?php

namespace App\Notifications;

use App\Models\StockTransaction;
use App\Models\ConsumableItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockInPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public StockTransaction $transaction,
        public ConsumableItem $item
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'stock_in_pending',
            'stock_transaction_id' => $this->transaction->id,
            'item_name' => $this->item->name,
            'qty' => $this->transaction->qty,
            'message' => "Transaksi masuk baru menunggu approval: {$this->item->name} ({$this->transaction->qty} {$this->item->unit})",
        ];
    }
}
