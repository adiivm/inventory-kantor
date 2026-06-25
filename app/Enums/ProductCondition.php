<?php

namespace App\Enums;

enum ProductCondition: string
{
    case Ready = 'ready';
    case Repair = 'repair';
    case Broken = 'broken';
    case Disposed = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Ready',
            self::Repair => 'Servis',
            self::Broken => 'Rusak',
            self::Disposed => 'Dibuang',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Ready => 'bg-success',
            self::Repair => 'bg-warning text-dark',
            self::Broken => 'bg-danger',
            self::Disposed => 'bg-dark',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
