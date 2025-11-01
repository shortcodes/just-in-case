<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Download extends Model
{
    protected $fillable = [
        'custodianship_id',
        'ip_address',
        'user_agent',
        'success',
        'filename',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
        ];
    }

    public function custodianship(): BelongsTo
    {
        return $this->belongsTo(Custodianship::class);
    }
}
