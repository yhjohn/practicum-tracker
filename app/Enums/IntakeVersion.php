<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IntakeVersion: string implements HasColor, HasLabel
{
    case V1 = 'v1';
    case V2 = 'v2';

    public function getLabel(): string
    {
        return match ($this) {
            self::V1 => 'V1',
            self::V2 => 'V2',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::V1 => 'gray',
            self::V2 => 'info',
        };
    }
}
