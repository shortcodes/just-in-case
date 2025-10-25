<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipient extends Model
{
    /** @use HasFactory<\Database\Factories\RecipientFactory> */
    use HasFactory;

    protected $fillable = [
        'custodianship_id',
        'email',
    ];

    public function custodianship(): BelongsTo
    {
        return $this->belongsTo(Custodianship::class);
    }
}
