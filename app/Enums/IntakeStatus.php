<?php

namespace App\Enums;

enum IntakeStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Submitted => 'Submitted',
        };
    }
}
