<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0; text-align: center; }
        h2 { font-size: 11px; text-transform: uppercase; background: #f3f4f6; padding: 4px 6px; margin: 14px 0 6px; }
        p { line-height: 1.5; margin: 6px 0; }
        .confidential { text-align: right; font-weight: bold; color: #6b7280; margin-bottom: 4px; }
        table.header { width: 100%; border-collapse: collapse; margin: 10px 0 4px; }
        table.header td { padding: 3px 0; vertical-align: top; }
        table.header td.label { width: 150px; font-weight: bold; }
        table.members { width: 100%; border-collapse: collapse; margin: 6px 0; }
        table.members th, table.members td { border: 1px solid #9ca3af; padding: 3px 5px; text-align: left; vertical-align: top; }
        table.members th { background: #f3f4f6; }
        .part-body { line-height: 1.5; }
        .part-body p { margin: 4px 0; }
        .signature { margin-top: 24px; }
        .signature td { padding: 6px 0; }
        .footnote { margin-top: 16px; font-size: 9px; font-style: italic; color: #6b7280; }
    </style>
</head>
<body>
    @php($rows = $log->memberRows())
    <p class="confidential">CONFIDENTIAL</p>
    <h1>Counseling Session Report</h1>

    <h2>Group &amp; Session Information</h2>
    <table class="header">
        <tr>
            <td class="label">Group Title / Code</td>
            <td>{{ $log->group?->name }}</td>
            <td class="label">Session Number</td>
            <td>{{ $log->session_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Session Theme</td>
            <td>{{ $log->session_theme ?? '-' }}</td>
            <td class="label">Counseling Approach / Theory</td>
            <td>{{ $log->counselling_approach ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td>{{ optional($log->session_date)->format('d M Y') ?? '-' }}</td>
            <td class="label">Time</td>
            <td>{{ optional($log->start_time)->format('H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Duration</td>
            <td>{{ $log->duration_hours ? $log->duration_hours.' hrs' : '-' }}</td>
            <td class="label">Venue / Organization</td>
            <td>{{ $log->place ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Total Sessions Planned</td>
            <td>{{ $log->total_sessions_planned ?? '-' }}</td>
            <td class="label">Group Size (No. of Members)</td>
            <td>{{ $rows->count() }}</td>
        </tr>
    </table>

    <h2>Group Facilitator</h2>
    <table class="header">
        <tr>
            <td class="label">Name of Facilitator / Trainee</td>
            <td>{{ $log->facilitator_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Role</td>
            <td>Student Counselor / Group Leader</td>
        </tr>
        <tr>
            <td class="label">Supervised by</td>
            <td>{{ $log->supervisor_name ?? '-' }}</td>
        </tr>
    </table>

    <h2>Group Member Information</h2>
    <table class="members">
        <tr><th>No.</th><th>Name / Client Code</th><th>Gender</th><th>Age</th><th>Attendance / Notes</th></tr>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['gender'] }}</td>
                <td>{{ $row['age'] }}</td>
                <td>{{ $row['attendance'] }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Part 1: Issues / Problems Presented</h2>
    <div class="part-body">{!! $log->part1_subjective_objective ?: '<p>-</p>' !!}</div>

    <h2>Part 2: Assessment of Issues / Problems</h2>
    <div class="part-body">{!! $log->part2_assessment ?: '<p>-</p>' !!}</div>

    <h2>Part 3: Treatment Planning and Action</h2>
    <div class="part-body">{!! $log->part3_treatment_plan ?: '<p>-</p>' !!}</div>

    <h2>Part 4: Evaluation of Counseling Sessions</h2>
    <p><strong>Individual Member Progress This Session</strong></p>
    <table class="members">
        <tr><th>No.</th><th>Name / Client Code</th><th>Participation Level (Low/Moderate/High)</th><th>Facilitator's Notes / Observations</th></tr>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['participation'] }}</td>
                <td>{{ $row['progress_notes'] }}</td>
            </tr>
        @endforeach
    </table>
    <div class="part-body">{!! $log->part4_evaluation ?: '<p>-</p>' !!}</div>

    <table class="header signature">
        <tr><td class="label">Student / Counselor Trainee Name</td><td>{{ $log->facilitator_name }}</td></tr>
        <tr><td class="label">Signature</td><td>@include('pdf.signature')</td></tr>
        <tr><td class="label">Date and Time</td><td>{{ optional($log->written_at)->format('d/m/Y H:i') }}</td></tr>
    </table>

    <p class="footnote">*Group counseling session reports must be prepared within 24 hours after each counseling session is conducted.</p>
</body>
</html>
