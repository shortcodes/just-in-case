<?php

namespace App\Notifications;

use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiredCustodianshipNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [
        60 * 60, // 1 hour
        60 * 60 * 24, // 1 day
        60 * 60 * 24 * 7, // 1 week
    ];

    public ?Delivery $delivery = null;

    public function __construct(
        public Custodianship $custodianship,
        public Recipient $recipient
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $userName = $this->custodianship->user->name ?? 'Someone';
        $hasAttachments = $this->custodianship->getMedia('attachments')->isNotEmpty();
        $downloadUrl = route('custodianships.download', ['custodianship' => $this->custodianship->uuid]);
        $appName = config('app.name');

        $message = (new MailMessage)
            ->subject(__('notifications.expired_custodianship.subject', ['userName' => $userName, 'appName' => $appName]))
            ->greeting(__('notifications.expired_custodianship.greeting', ['userName' => $userName, 'appName' => $appName]))
            ->line('**'.__('notifications.expired_custodianship.message_header').'**')
            ->line('');

        if ($this->delivery) {
            $message->withSymfonyMessage(function ($message) {
                $message->getHeaders()->addIdHeader('Message-ID', $this->delivery->mailgun_message_id);
            });
        }

        if ($this->custodianship->message && $this->custodianship->message->content) {
            $lines = explode("\n", $this->custodianship->message->content);
            foreach ($lines as $line) {
                $message->line($line);
            }
        }

        if ($hasAttachments) {
            $message->line('')
                ->line(__('notifications.expired_custodianship.download_attachments'))
                ->action(__('notifications.expired_custodianship.download_button'), $downloadUrl);
        }

        $disclaimerHtml = view('vendor.mail.html.disclaimer', [
            'url' => config('app.url'),
        ])->render();

        $message->line(new \Illuminate\Support\HtmlString($disclaimerHtml));

        return $message;
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications',
        ];
    }

    public function failed(\Throwable $exception): void
    {
        if ($this->delivery) {
            $this->delivery->update(['status' => 'failed']);

            return;
        }

        Delivery::query()
            ->where('custodianship_id', $this->custodianship->id)
            ->where('recipient_id', $this->recipient->id)
            ->where('status', 'pending')
            ->latest()
            ->first()
            ?->update(['status' => 'failed']);
    }
}
