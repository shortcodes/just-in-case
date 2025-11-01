<?php

namespace App\Notifications;

use App\Models\Custodianship;
use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustodianshipOwnerAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Custodianship $custodianship,
        public Delivery $delivery,
        public string $type,
        public string $errorMessage
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');

        $subject = match ($this->type) {
            'bounced' => "{$appName} - Email Bounced",
            'failed' => "{$appName} - Delivery Failed",
            default => "{$appName} - Delivery Alert",
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->error()
            ->greeting('Delivery Problem for Your Custodianship')
            ->line("The message for your custodianship '{$this->custodianship->name}' was not delivered to {$this->delivery->recipient_email}.")
            ->line('')
            ->line("Reason: {$this->errorMessage}")
            ->line('')
            ->line('Please check the recipient email address and update your custodianship if needed.')
            ->action('View Custodianship', route('custodianships.show', $this->custodianship));

        return $message;
    }
}
