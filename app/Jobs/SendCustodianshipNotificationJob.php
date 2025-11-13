<?php

namespace App\Jobs;

use App\Models\Custodianship;
use App\Models\Delivery;
use App\Models\Recipient;
use App\Notifications\ExpiredCustodianshipNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class SendCustodianshipNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Custodianship $custodianship,
        public Recipient $recipient,
        public int $attemptNumber = 1
    ) {}

    public function handle(): void
    {
        $delivery = Delivery::query()
            ->where('custodianship_id', $this->custodianship->id)
            ->where('recipient_id', $this->recipient->id)
            ->first();

        if (! $delivery) {
            $delivery = $this->createDeliveryRecord();
        } else {
            $this->updateDeliveryForRetry($delivery);
        }

        $notification = new ExpiredCustodianshipNotification(
            $this->custodianship,
            $this->recipient
        );
        $notification->delivery = $delivery;

        $this->recipient->notify($notification);
    }

    protected function createDeliveryRecord(): Delivery
    {
        $mailgunDomain = config('mail.mailers.mailgun.domain') ?: config('mail.from.address');
        $mailgunMessageId = Str::random(32).'@'.preg_replace('/^[^@]*@/', '', $mailgunDomain);

        return Delivery::create([
            'custodianship_id' => $this->custodianship->id,
            'recipient_id' => $this->recipient->id,
            'recipient_email' => $this->recipient->email,
            'mailgun_message_id' => $mailgunMessageId,
            'status' => 'pending',
            'attempt_number' => $this->attemptNumber,
            'max_attempts' => config('custodianship.delivery.max_attempts', 3),
        ]);
    }

    protected function updateDeliveryForRetry(Delivery $delivery): void
    {
        $delivery->update([
            'attempt_number' => $this->attemptNumber,
            'last_retry_at' => now(),
            'next_retry_at' => null,
            'error_message' => null,
        ]);
    }
}
