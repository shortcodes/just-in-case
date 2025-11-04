<?php

namespace App\Models;

use App\Notifications\CustodianshipOwnerAlert;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    /** @use HasFactory<\Database\Factories\DeliveryFactory> */
    use HasFactory;

    protected $fillable = [
        'custodianship_id',
        'recipient_id',
        'recipient_email',
        'mailgun_message_id',
        'status',
        'delivered_at',
        'attempt_number',
        'max_attempts',
        'last_retry_at',
        'next_retry_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'last_retry_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function custodianship(): BelongsTo
    {
        return $this->belongsTo(Custodianship::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    public function canRetry(): bool
    {
        return $this->attempt_number < $this->max_attempts
            && $this->status !== 'delivered';
    }

    public function fail(string $message, bool $permanent = false): void
    {
        $this->update(['error_message' => $message]);

        if ($permanent) {
            $this->update([
                'status' => 'failed',
                'next_retry_at' => null,
            ]);
            $this->custodianship->updateDeliveryStatus();
            $this->sendOwnerAlert($message);

            return;
        }

        if (! $this->canRetry()) {
            $this->update([
                'status' => 'failed',
                'next_retry_at' => null,
            ]);
            $this->custodianship->updateDeliveryStatus();
            $this->sendOwnerAlert($message);

            return;
        }

        $intervals = config('custodianship.delivery.retry_intervals');
        $intervalIndex = $this->attempt_number - 1;
        $delaySeconds = $intervals[$intervalIndex] ?? end($intervals);

        $this->update(['next_retry_at' => now()->addSeconds($delaySeconds)]);
    }

    protected function sendOwnerAlert(string $errorMessage): void
    {
        if ($this->custodianship && $this->custodianship->user) {
            $this->custodianship->user->notify(
                new CustodianshipOwnerAlert(
                    $this->custodianship,
                    $this,
                    'failed',
                    $errorMessage
                )
            );
        }
    }

    public function getLastAttemptedAtAttribute()
    {
        return $this->last_retry_at ?? $this->created_at;
    }

    public function scopeStale($query)
    {
        $timeout = config('custodianship.delivery.pending_timeout');

        return $query->where('status', 'pending')
            ->whereNull('next_retry_at')
            ->whereRaw('COALESCE(last_retry_at, created_at) < ?', [now()->subSeconds($timeout)]);
    }
}
