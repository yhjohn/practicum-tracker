<?php

namespace App\Models;

use App\Enums\ConsentStatus;
use Database\Factories\ConsentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    /** @use HasFactory<ConsentFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'agree' => 'boolean',
        'signature_date' => 'date',
        'submitted_at' => 'datetime',
        'status' => ConsentStatus::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
