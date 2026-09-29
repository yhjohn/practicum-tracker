<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportSummary extends Model
{
    protected $guarded = [];

    public function summarizable(): MorphTo
    {
        return $this->morphTo();
    }
}
