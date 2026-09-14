<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->withPivot(['joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function individualSessions(): MorphMany
    {
        return $this->morphMany(CounsellingSession::class, 'sessionable');
    }

    public function attendedSessions(): BelongsToMany
    {
        return $this->belongsToMany(CounsellingSession::class, 'session_attendance')
            ->withPivot(['attended', 'notes'])
            ->withTimestamps();
    }

    public function intakes(): HasMany
    {
        return $this->hasMany(Intake::class);
    }

    public function consent(): HasOne
    {
        return $this->hasOne(Consent::class);
    }
}
