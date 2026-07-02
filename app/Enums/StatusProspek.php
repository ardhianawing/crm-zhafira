<?php

namespace App\Enums;

enum StatusProspek: string
{
    case New = 'New';
    case Cold = 'Cold';
    case Warm = 'Warm';
    case Hot = 'Hot';
    case Deal = 'Deal';
    case TidakRespon = 'Tidak Respon';
    case TidakBerminat = 'Tidak Berminat';

    public function label(): string
    {
        return match($this) {
            self::New => 'New',
            self::Cold => 'Cold',
            self::Warm => 'Warm',
            self::Hot => 'Hot',
            self::Deal => 'Deal',
            self::TidakRespon => 'Tidak Respon',
            self::TidakBerminat => 'Tidak Berminat',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::New => 'secondary',
            self::Cold => 'info',
            self::Warm => 'warning',
            self::Hot => 'danger',
            self::Deal => 'success',
            self::TidakRespon => 'secondary',
            self::TidakBerminat => 'dark',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
