<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustodianshipCollectionResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
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
            'recipientsCount' => $this->recipients_count ?? 0,
            'attachmentsCount' => 0, // TODO: implement when media library is added
            'lastResetAt' => $this->last_reset_at?->toISOString(),
            'nextTriggerAt' => $this->next_trigger_at?->toISOString(),
            'activatedAt' => $this->activated_at?->toISOString(),
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
        ];
    }
}
