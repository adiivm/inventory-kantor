<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Archive = 'archive';
    case Sold = 'jual';
    case Destroyed = 'destroy';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Archive => 'Archived',
            self::Sold => 'Sold',
            self::Destroyed => 'Destroyed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
