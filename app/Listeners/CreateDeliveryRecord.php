<?php

namespace App\Listeners;

use App\Models\Delivery;
use App\Notifications\ExpiredCustodianshipNotification;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Str;

class CreateDeliveryRecord
{
    public function handle(NotificationSending $event): void
    {
        if (! $event->notification instanceof ExpiredCustodianshipNotification) {
            return;
        }

        if ($event->channel !== 'mail') {
            return;
        }

        $notification = $event->notification;

        $messageId = sprintf(
            '%s@%s',
            Str::random(32),
            config('mail.mailers.mailgun.domain', 'localhost')
        );

        $delivery = Delivery::create([
            'custodianship_id' => $notification->custodianship->id,
            'recipient_id' => $notification->recipient->id,
            'recipient_email' => $notification->recipient->email,
            'mailgun_message_id' => $messageId,
            'status' => 'pending',
        ]);

        $notification->delivery = $delivery;
    }
}
