<?php

namespace App\Models;

use Database\Factories\ProgressJourneyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressJourney extends Model
{
    /** @use HasFactory<ProgressJourneyFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'ai_drafted_at' => 'datetime',
    ];

    /**
     * Written by Claude and not edited by hand since, so it may be redrafted.
     */
    public function isUntouchedAiDraft(): bool
    {
        return $this->ai_drafted_at !== null && $this->updated_at->lte($this->ai_drafted_at);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function individualCounsellingLog(): BelongsTo
    {
        return $this->belongsTo(IndividualCounsellingLog::class);
    }
}
