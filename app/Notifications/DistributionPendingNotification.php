<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DistributionPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public $distribution
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'distribution_pending',
            'distribution_id' => $this->distribution->id,
            'reference_number' => $this->distribution->reference_number,
            'requester_name' => $this->distribution->requester_name,
            'message' => "Permintaan distribusi baru menunggu approval: {$this->distribution->reference_number}",
        ];
    }
}
