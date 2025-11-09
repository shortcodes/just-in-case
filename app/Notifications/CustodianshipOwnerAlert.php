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
            'bounced' => __('notifications.custodianship_owner_alert.subject_bounced', ['appName' => $appName]),
            'failed' => __('notifications.custodianship_owner_alert.subject_failed', ['appName' => $appName]),
            default => __('notifications.custodianship_owner_alert.subject_default', ['appName' => $appName]),
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->error()
            ->greeting(__('notifications.custodianship_owner_alert.greeting'))
            ->line(__('notifications.custodianship_owner_alert.not_delivered', [
                'custodianshipName' => $this->custodianship->name,
                'recipientEmail' => $this->delivery->recipient_email,
            ]))
            ->line('')
            ->line(__('notifications.custodianship_owner_alert.reason', ['errorMessage' => $this->errorMessage]))
            ->line('')
            ->line(__('notifications.custodianship_owner_alert.check_email'))
            ->action(__('notifications.custodianship_owner_alert.view_button'), route('custodianships.show', $this->custodianship));

        return $message;
    }
}
