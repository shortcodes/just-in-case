<?php

namespace App\Enums;

enum IntervalUnit: string
{
    case MINUTES = 'minutes';
    case HOURS = 'hours';
    case DAYS = 'days';

    public function label(): string
    {
        return match ($this) {
            self::MINUTES => 'Minutes',
            self::HOURS => 'Hours',
            self::DAYS => 'Days',
        };
    }

    public function toIso8601(int $value): string
    {
        return match ($this) {
            self::MINUTES => "PT{$value}M",
            self::HOURS => "PT{$value}H",
            self::DAYS => "P{$value}D",
        };
    }

    public static function toArray(): array
    {
        return array_map(
            fn (self $unit) => [
                'value' => $unit->value,
                'label' => $unit->label(),
            ],
            self::cases()
        );
    }
}
