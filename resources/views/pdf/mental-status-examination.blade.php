<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0; text-align: center; }
        h2 { font-size: 11px; text-transform: uppercase; background: #f3f4f6; padding: 4px 6px; margin: 14px 0 6px; }
        .confidential { text-align: right; font-weight: bold; color: #6b7280; margin-bottom: 4px; }
        table.header { width: 100%; border-collapse: collapse; margin: 10px 0 4px; }
        table.header td { padding: 3px 0; vertical-align: top; }
        table.header td.label { width: 160px; font-weight: bold; }
        table.rows { width: 100%; border-collapse: collapse; }
        table.rows td { padding: 3px 0; vertical-align: top; }
        table.rows td.label { width: 220px; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $join = fn (?array $state): string => filled($state) ? implode(', ', $state) : '-';
    @endphp

    <p class="confidential">CONFIDENTIAL</p>
    <h1>Mental Status Examination / Behavioral Observations</h1>

    <table class="header">
        <tr>
            <td class="label">Client Name</td>
            <td>{{ $mse->client?->name_with_code }}</td>
            <td class="label">Gender</td>
            <td>{{ $mse->client?->gender ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date of Birth</td>
            <td>{{ optional($mse->client?->dob)->format('d M Y') ?? '-' }}</td>
            <td class="label">Age</td>
            <td>{{ $mse->client?->age ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date of Interview</td>
            <td>{{ optional($mse->date_of_interview)->format('d M Y') ?? '-' }}</td>
            <td class="label"></td>
            <td></td>
        </tr>
    </table>

    <h2>1. Dress/Grooming</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->dress_grooming) }}</td></tr>
        <tr><td class="label">Other</td><td>{{ $mse->dress_grooming_other ?: '-' }}</td></tr>
    </table>

    <h2>2. Gross/Fine Motor Function</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->motor_function) }}</td></tr>
        <tr><td class="label">Manual dexterity</td><td>{{ $join($mse->manual_dexterity) }}</td></tr>
        <tr><td class="label">Other</td><td>{{ $mse->motor_function_other ?: '-' }}</td></tr>
    </table>

    <h2>3. Eye Contact</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->eye_contact) }}</td></tr>
        <tr><td class="label">Other</td><td>{{ $mse->eye_contact_other ?: '-' }}</td></tr>
    </table>

    <h2>4. Vision</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->vision) }}</td></tr>
        <tr><td class="label">Detail</td><td>{{ $mse->vision_detail ?: '-' }}</td></tr>
    </table>

    <h2>5. Hearing</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->hearing) }}</td></tr>
        <tr><td class="label">Detail</td><td>{{ $mse->hearing_detail ?: '-' }}</td></tr>
    </table>

    <h2>6. Verbal Comprehension</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->verbal_comprehension) }}</td></tr>
        <tr><td class="label">Detail</td><td>{{ $mse->verbal_comprehension_detail ?: '-' }}</td></tr>
    </table>

    <h2>7. Orientation</h2>
    <table class="rows">
        <tr><td class="label">Day of week / Month / Date / Year / Season</td>
            <td>{{ collect([$mse->orientation_day_of_week, $mse->orientation_month, $mse->orientation_date, $mse->orientation_year, $mse->orientation_season])->filter()->implode(' / ') ?: '-' }}</td></tr>
        <tr><td class="label">Building type / Floor</td>
            <td>{{ collect([$mse->orientation_building_type, $mse->orientation_floor])->filter()->implode(' / ') ?: '-' }}</td></tr>
        <tr><td class="label">Town / County / State</td>
            <td>{{ collect([$mse->orientation_town, $mse->orientation_county, $mse->orientation_state])->filter()->implode(' / ') ?: '-' }}</td></tr>
        <tr><td class="label">Orientation to situation</td><td>{{ $mse->orientation_situation_rating ?: '-' }}</td></tr>
        <tr><td class="label">Situation notes</td><td>{{ $mse->orientation_situation_notes ?: '-' }}</td></tr>
    </table>

    <h2>8. Speech</h2>
    <table class="rows">
        <tr><td class="label">Fluency/flow</td><td>{{ $join($mse->speech_fluency) }}</td></tr>
        <tr><td class="label">Rate</td><td>{{ $join($mse->speech_rate) }}</td></tr>
        <tr><td class="label">Volume</td><td>{{ $join($mse->speech_volume) }}</td></tr>
        <tr><td class="label">Prosody/intonation</td><td>{{ $join($mse->speech_prosody) }}</td></tr>
        <tr><td class="label">Other</td><td>{{ $mse->speech_prosody_other ?: '-' }}</td></tr>
    </table>

    <h2>9. Affect</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->affect) }}</td></tr>
    </table>

    <h2>10. Mood</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->mood) }}</td></tr>
        <tr><td class="label">Other</td><td>{{ $mse->mood_other ?: '-' }}</td></tr>
    </table>

    <h2>11. Sleep</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->sleep) }}</td></tr>
    </table>

    <h2>12. Appetite</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->appetite) }}</td></tr>
    </table>

    <h2>13. Thought Process</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->thought_process) }}</td></tr>
        <tr><td class="label">Other</td><td>{{ $mse->thought_process_other ?: '-' }}</td></tr>
    </table>

    <h2>14. Thought Content</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->thought_content) }}</td></tr>
        <tr><td class="label">Delusions</td><td>{{ $mse->thought_content_delusions_detail ?: '-' }}</td></tr>
        <tr><td class="label">Hallucinations</td><td>{{ $mse->thought_content_hallucinations_detail ?: '-' }}</td></tr>
        <tr><td class="label">Other</td><td>{{ $mse->thought_content_other_detail ?: '-' }}</td></tr>
    </table>

    <h2>15. Motivation/Effort on Testing</h2>
    <table class="rows">
        <tr><td class="label">Observations</td><td>{{ $join($mse->motivation_effort) }}</td></tr>
    </table>

    <h2>16. Strengths</h2>
    <p>{{ $mse->strengths ?: '-' }}</p>

    <h2>17. Any Other Notable Behavioral Observations</h2>
    <p>{{ $mse->other_observations ?: '-' }}</p>
</body>
</html>
