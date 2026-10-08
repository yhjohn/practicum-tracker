<?php

namespace App\Models;

use App\Enums\ConsentStatus;
use Database\Factories\GroupConsentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupConsent extends Model
{
    /** @use HasFactory<GroupConsentFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
        'agree' => 'boolean',
        'signature_date' => 'date',
        'submitted_at' => 'datetime',
        'status' => ConsentStatus::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
