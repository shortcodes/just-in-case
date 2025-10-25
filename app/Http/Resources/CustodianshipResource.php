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
            'interval' => $this->interval,
            'lastResetAt' => $this->last_reset_at?->toISOString(),
            'nextTriggerAt' => $this->next_trigger_at?->toISOString(),
            'activatedAt' => $this->activated_at?->toISOString(),
            'recipients' => RecipientResource::collection($this->whenLoaded('recipients')),
            'messageContent' => $this->whenLoaded('message', fn () => $this->message?->content),
            'attachments' => [],
            'resetCount' => $this->when($this->relationLoaded('resets'), fn () => $this->resets->count()),
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
        ];
    }
}
