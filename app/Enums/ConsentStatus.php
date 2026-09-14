<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ConsentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Submitted = 'submitted';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Submitted => 'Submitted',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Submitted => 'success',
        };
    }
}
