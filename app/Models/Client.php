<?php

namespace App\Models;

use App\Enums\IntakeStatus;
use App\Enums\IntakeVersion;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @property Consent $consent
 * @property Intake $intake
 * @property IntakeV2 $intakeV2
 */
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
        'public_access_expires_at' => 'datetime',
        'intake_version' => IntakeVersion::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client): void {
            if (empty($client->public_access_token)) {
                $client->public_access_token = (string) Str::uuid();
            }

            if (empty($client->public_access_code)) {
                $client->public_access_code = Hash::make((string) random_int(100000, 999999));
            }

            if (empty($client->public_access_expires_at)) {
                $client->public_access_expires_at = now()->addDays(30);
            }

            if (empty($client->intake_version)) {
                $client->intake_version = IntakeVersion::V2;
            }
        });
    }

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

    public function intake(): HasOne
    {
        return $this->hasOne(Intake::class);
    }

    public function intakeV2(): HasOne
    {
        return $this->hasOne(IntakeV2::class);
    }

    /**
     * Resolve this client's current intake, creating a pending one prefilled
     * from known client details if it doesn't exist yet.
     */
    public function currentIntake(): Intake|IntakeV2
    {
        if ($this->intake_version === IntakeVersion::V1) {
            return $this->intake ?? $this->intake()->create([
                'status' => IntakeStatus::Pending,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'dob' => $this->dob,
                'emergency_name' => $this->emergency_contact,
                'emergency_phone' => $this->emergency_contact_phone,
            ]);
        }

        return $this->intakeV2 ?? $this->intakeV2()->create([
            'status' => IntakeStatus::Pending,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);
    }

    public function consent(): HasOne
    {
        return $this->hasOne(Consent::class);
    }
}
