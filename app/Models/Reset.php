<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reset extends Model
{
    /** @use HasFactory<\Database\Factories\ResetFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'custodianship_id',
        'user_id',
        'reset_method',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function custodianship(): BelongsTo
    {
        return $this->belongsTo(Custodianship::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
