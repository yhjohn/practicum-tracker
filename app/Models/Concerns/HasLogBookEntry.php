<?php

namespace App\Models\Concerns;

use App\Models\LogBookEntry;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Keeps a counselling log mirrored as one row in the practicum log book.
 */
trait HasLogBookEntry
{
    public static function bootHasLogBookEntry(): void
    {
        static::saved(fn (self $log) => LogBookEntry::syncFrom($log));
        static::deleted(fn (self $log) => $log->logBookEntry()->delete());
    }

    public function logBookEntry(): MorphOne
    {
        return $this->morphOne(LogBookEntry::class, 'loggable');
    }
}
