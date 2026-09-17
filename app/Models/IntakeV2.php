<?php

namespace App\Models;

use App\Enums\IntakeStatus;
use Database\Factories\IntakeV2Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntakeV2 extends Model
{
    /** @use HasFactory<IntakeV2Factory> */
    use HasFactory;

    protected $table = 'intake_v2s';

    protected $guarded = [];

    protected $hidden = [
        'access_code',
    ];

    protected $casts = [
        'signature_date' => 'date',
        'submitted_at' => 'datetime',
        'enjoyable_activities' => 'array',
        'personal_strengths' => 'array',
        'coping_resources' => 'array',
        'family_relationships' => 'array',
        'childhood_experiences' => 'array',
        'beliefs' => 'array',
        'substances_used' => 'array',
        'psychiatric_services' => 'array',
        'mood_recent' => 'array',
        'current_stressors' => 'array',
        'counselling_goals' => 'array',
        'status' => IntakeStatus::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
