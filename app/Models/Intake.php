<?php

namespace App\Models;

use App\Enums\IntakeStatus;
use Database\Factories\IntakeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intake extends Model
{
    /** @use HasFactory<IntakeFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'access_code',
    ];

    protected $casts = [
        'dob' => 'date',
        'signature_date' => 'date',
        'submitted_at' => 'datetime',
        'marital' => 'array',
        'education_current' => 'array',
        'trauma' => 'array',
        'status' => IntakeStatus::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
