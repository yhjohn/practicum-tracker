<?php

namespace App\Models;

use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Group extends Model
{
    /** @use HasFactory<GroupFactory> */
    use HasFactory;

    protected $guarded = [];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class)
            ->withPivot(['joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function sessions(): MorphMany
    {
        return $this->morphMany(CounsellingSession::class, 'sessionable');
    }

    public function counsellingLogs(): HasMany
    {
        return $this->hasMany(GroupCounsellingLog::class);
    }
}
