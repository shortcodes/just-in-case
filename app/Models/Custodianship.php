<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Custodianship extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\CustodianshipFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'interval',
        'last_reset_at',
        'next_trigger_at',
        'activated_at',
    ];

    protected $appends = [
        'delivery_status',
        'delivery_stats',
    ];

    protected function casts(): array
    {
        return [
            'last_reset_at' => 'datetime',
            'next_trigger_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($custodianship) {
            if (empty($custodianship->uuid)) {
                $custodianship->uuid = (string) Str::uuid();
            }

            if (empty($custodianship->status)) {
                $custodianship->status = 'draft';
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function message(): HasOne
    {
        return $this->hasOne(CustodianshipMessage::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function resets(): HasMany
    {
        return $this->hasMany(Reset::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk(config('media-library.disk_name'));
    }

    public function scopeOrderByDefault(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->orderByRaw("
                CASE status
                    WHEN 'active' THEN
                        CASE
                            WHEN next_trigger_at IS NULL OR next_trigger_at >= ? THEN 0
                            WHEN next_trigger_at < ? THEN 1
                            ELSE 1
                        END
                    WHEN 'draft' THEN 2
                    WHEN 'completed' THEN 3
                    ELSE 4
                END
            ", [$now, $now])
            ->orderByRaw("
                CASE
                    WHEN status = 'active' THEN next_trigger_at
                    ELSE NULL
                END ASC
            ")
            ->orderByRaw("
                CASE
                    WHEN status IN ('draft', 'completed') THEN created_at
                    ELSE NULL
                END DESC
            ");
    }

    public function getDeliveryStatusAttribute(): ?string
    {
        if ($this->status !== 'completed') {
            return null;
        }

        if ($this->deliveries()->count() === 0) {
            return null;
        }

        $stats = $this->getDeliveryStatsAttribute();

        if ($stats['pending'] > 0) {
            return 'dispatched';
        }

        if ($stats['delivered'] === $stats['total']) {
            return 'delivered';
        }

        if ($stats['failed'] === $stats['total']) {
            return 'failed';
        }

        if ($stats['failed'] > 0 && $stats['delivered'] > 0) {
            return 'partially_failed';
        }

        if ($stats['delivered'] > 0 && $stats['pending'] > 0) {
            return 'partially_delivered';
        }

        return null;
    }

    public function getDeliveryStatsAttribute(): array
    {
        $deliveries = $this->deliveries;
        $total = $deliveries->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'delivered' => 0,
                'failed' => 0,
                'pending' => 0,
                'success_percentage' => 0,
            ];
        }

        $delivered = $deliveries->where('status', 'delivered')->count();
        $failed = $deliveries->where('status', 'failed')->count();
        $pending = $deliveries->where('status', 'pending')->count();

        return [
            'total' => $total,
            'delivered' => $delivered,
            'failed' => $failed,
            'pending' => $pending,
            'success_percentage' => $total > 0 ? round(($delivered / $total) * 100, 2) : 0,
        ];
    }

    public function updateDeliveryStatus(): void
    {
        $stats = $this->delivery_stats;

        if ($stats['total'] === 0) {
            return;
        }

        if ($stats['delivered'] === $stats['total']) {
            $this->update(['status' => 'completed']);

            return;
        }

        if ($stats['total'] === $stats['delivered'] + $stats['failed']) {
            $this->update(['status' => 'completed']);

            return;
        }
    }
}
