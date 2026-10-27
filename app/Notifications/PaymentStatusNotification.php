<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $eventKey,
        public readonly int $paymentId,
        public readonly string $event,
        public readonly string $message,
        public readonly string $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_key' => $this->eventKey,
            'payment_id' => $this->paymentId,
            'event' => $this->event,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
