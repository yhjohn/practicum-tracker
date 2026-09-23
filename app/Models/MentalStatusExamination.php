<?php

namespace App\Models;

use Database\Factories\MentalStatusExaminationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentalStatusExamination extends Model
{
    /** @use HasFactory<MentalStatusExaminationFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date_of_interview' => 'date',
        'dress_grooming' => 'array',
        'motor_function' => 'array',
        'manual_dexterity' => 'array',
        'eye_contact' => 'array',
        'vision' => 'array',
        'hearing' => 'array',
        'verbal_comprehension' => 'array',
        'speech_fluency' => 'array',
        'speech_rate' => 'array',
        'speech_volume' => 'array',
        'speech_prosody' => 'array',
        'affect' => 'array',
        'mood' => 'array',
        'sleep' => 'array',
        'appetite' => 'array',
        'thought_process' => 'array',
        'thought_content' => 'array',
        'motivation_effort' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
