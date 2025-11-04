<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustodianshipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Used for individual custodianship (show page) - includes full data.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status,
            'deliveryStatus' => $this->delivery_status,
            'interval' => $this->interval,
            'intervalDays' => $this->parseIntervalToDays($this->interval),
            'lastResetAt' => $this->last_reset_at?->toISOString(),
            'nextTriggerAt' => $this->next_trigger_at?->toISOString(),
            'activatedAt' => $this->activated_at?->toISOString(),
            'recipientsCount' => $this->when(
                $this->relationLoaded('recipients'),
                fn () => $this->recipients->count()
            ),
            'recipients' => $this->when(
                $this->relationLoaded('recipients'),
                fn () => $this->recipients->map(fn ($recipient) => [
                    'id' => $recipient->id,
                    'email' => $recipient->email,
                    'createdAt' => $recipient->created_at->toISOString(),
                    'latestDelivery' => $recipient->relationLoaded('latestDelivery') && $recipient->latestDelivery
                        ? [
                            'id' => $recipient->latestDelivery->id,
                            'status' => $recipient->latestDelivery->status,
                            'mailgunMessageId' => $recipient->latestDelivery->mailgun_message_id,
                            'recipientEmail' => $recipient->latestDelivery->recipient_email,
                            'deliveredAt' => $recipient->latestDelivery->delivered_at?->toISOString(),
                            'attemptNumber' => $recipient->latestDelivery->attempt_number,
                            'maxAttempts' => $recipient->latestDelivery->max_attempts,
                            'lastRetryAt' => $recipient->latestDelivery->last_retry_at?->toISOString(),
                            'nextRetryAt' => $recipient->latestDelivery->next_retry_at?->toISOString(),
                            'errorMessage' => $recipient->latestDelivery->error_message,
                            'createdAt' => $recipient->latestDelivery->created_at->toISOString(),
                            'updatedAt' => $recipient->latestDelivery->updated_at->toISOString(),
                        ]
                        : null,
                ])->toArray()
            ),
            'messageContent' => $this->whenLoaded('message', fn () => $this->message?->content),
            'attachments' => [],
            'resetCount' => $this->when($this->relationLoaded('resets'), fn () => $this->resets->count()),
            'user' => $this->when($this->relationLoaded('user'), fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
        ];
    }

    private function parseIntervalToDays(string $interval): float
    {
        try {
            $dateInterval = new \DateInterval($interval);

            $days = $dateInterval->d + ($dateInterval->m * 30) + ($dateInterval->y * 365);
            $hours = $dateInterval->h / 24;
            $minutes = $dateInterval->i / (24 * 60);

            return $days + $hours + $minutes;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
