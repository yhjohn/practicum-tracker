<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

trait HasPracticumWeek
{
    /**
     * Practicum week (1-based) of session_date, or null when there is no date
     * or it falls before the practicum start date.
     */
    protected function week(): Attribute
    {
        return Attribute::get(function (): ?int {
            if ($this->session_date === null) {
                return null;
            }

            $start = Carbon::parse(config('practicum.start_date'))->startOfDay();
            $days = (int) $start->diffInDays($this->session_date->copy()->startOfDay(), false);

            return $days < 0 ? null : intdiv($days, 7) + 1;
        });
    }
}
