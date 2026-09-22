<?php

namespace App\Models;

use App\Models\Concerns\HasPracticumWeek;
use App\Models\Concerns\HasWrittenAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualCounsellingLog extends Model
{
    use HasPracticumWeek;
    use HasWrittenAt;

    protected $guarded = [];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'written_at' => 'datetime',
        'attachments' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
