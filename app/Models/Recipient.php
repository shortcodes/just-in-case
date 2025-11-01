<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Recipient extends Model
{
    /** @use HasFactory<\Database\Factories\RecipientFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'custodianship_id',
        'email',
    ];

    public function custodianship(): BelongsTo
    {
        return $this->belongsTo(Custodianship::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function latestDelivery(): HasOne
    {
        return $this->hasOne(Delivery::class)->latestOfMany();
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
