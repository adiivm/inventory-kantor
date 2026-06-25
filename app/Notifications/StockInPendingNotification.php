<?php

namespace App\Notifications;

use App\Models\ConsumableItem;
use App\Models\StockTransaction;
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
        $typeLabel = $this->transaction->type === 'adjustment' ? 'Penyesuaian' : 'Transaksi masuk';

        return [
            'type' => 'stock_in_pending',
            'stock_transaction_id' => $this->transaction->id,
            'item_name' => $this->item->name,
            'qty' => $this->transaction->qty,
            'transaction_type' => $this->transaction->type,
            'message' => "{$typeLabel} baru menunggu approval: {$this->item->name} ({$this->transaction->qty} {$this->item->unit})",
        ];
    }
}
