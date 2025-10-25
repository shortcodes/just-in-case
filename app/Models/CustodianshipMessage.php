<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustodianshipMessage extends Model
{
    /** @use HasFactory<\Database\Factories\CustodianshipMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'custodianship_id',
        'content',
    ];

    public function custodianship(): BelongsTo
    {
        return $this->belongsTo(Custodianship::class);
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => decrypt($value),
            set: fn ($value) => encrypt($value),
        );
    }
}
