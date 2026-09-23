<?php

namespace App\Models\Concerns;

use App\Models\LogBookEntry;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Keeps a counselling log mirrored in the practicum log book: one counselling
 * row plus one hour of management and administration.
 */
trait HasLogBookEntry
{
    public static function bootHasLogBookEntry(): void
    {
        static::saved(fn (self $log) => LogBookEntry::syncFrom($log));
        static::deleted(fn (self $log) => $log->logBookEntries()->delete());
    }

    public function logBookEntries(): MorphMany
    {
        return $this->morphMany(LogBookEntry::class, 'loggable');
    }
}
