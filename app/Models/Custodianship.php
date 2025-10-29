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

class Custodianship extends Model
{
    /** @use HasFactory<\Database\Factories\CustodianshipFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'delivery_status',
        'interval',
        'last_reset_at',
        'next_trigger_at',
        'activated_at',
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
}
