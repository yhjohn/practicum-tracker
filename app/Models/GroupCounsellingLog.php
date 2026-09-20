<?php

namespace App\Models;

use App\Models\Concerns\HasPracticumWeek;
use Database\Factories\GroupCounsellingLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class GroupCounsellingLog extends Model
{
    /** @use HasFactory<GroupCounsellingLogFactory> */
    use HasFactory;

    use HasPracticumWeek;

    protected $guarded = [];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'written_at' => 'datetime',
        'members' => 'array',
        'attachments' => 'array',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * One row per group member for the docx/PDF member tables. Gender and age
     * come from the client's intake, falling back to their date of birth.
     *
     * @return Collection<int, array{no: int, name: string, gender: string, age: string, attendance: string, participation: string, progress_notes: string}>
     */
    public function memberRows(): Collection
    {
        $members = collect($this->members)->values();
        $clients = Client::with(['intake', 'intakeV2'])
            ->findMany($members->pluck('client_id'))
            ->keyBy('id');

        return $members->map(function (array $member, int $index) use ($clients): array {
            $client = $clients->get($member['client_id']);
            $attendance = ($member['attended'] ?? true) ? 'Present' : 'Absent';

            if (filled($member['attendance_notes'] ?? null)) {
                $attendance .= ' – '.$member['attendance_notes'];
            }

            return [
                'no' => $index + 1,
                'name' => $client?->name ?? '',
                'gender' => (string) ($client?->intakeV2?->sex ?? $client?->intake?->sex ?? ''),
                'age' => (string) ($client?->intakeV2?->age ?? $client?->intake?->age ?? $client?->dob?->age ?? ''),
                'attendance' => $attendance,
                'participation' => (string) ($member['participation_level'] ?? ''),
                'progress_notes' => (string) ($member['progress_notes'] ?? ''),
            ];
        });
    }
}
