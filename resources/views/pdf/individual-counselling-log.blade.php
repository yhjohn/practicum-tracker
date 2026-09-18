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
        table.header td.label { width: 140px; font-weight: bold; }
        .part-body { line-height: 1.5; }
        .part-body p { margin: 4px 0; }
        .signature-box { border: 1px solid #d1d5db; padding: 8px; margin-top: 6px; }
        .signature-box img { height: 80px; }
        table.summary { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.summary td { padding: 6px 0; vertical-align: top; }
        table.summary td.label { width: 160px; font-weight: bold; }
    </style>
</head>
<body>
    <p class="confidential">CONFIDENTIAL</p>
    <h1>Counseling Session Report</h1>

    <table class="header">
        <tr>
            <td class="label">Client Name / Code</td>
            <td>{{ $log->client?->name }}</td>
            <td class="label">Date</td>
            <td>{{ optional($log->session_date)->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Session</td>
            <td>{{ $log->session_number ?? '-' }}</td>
            <td class="label">Time</td>
            <td>{{ optional($log->start_time)->format('H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Duration</td>
            <td>{{ $log->duration_hours ? $log->duration_hours.' hrs' : '-' }}</td>
            <td class="label">Place / Organization</td>
            <td>{{ $log->place ?? '-' }}</td>
        </tr>
    </table>

    <h2>Part 1: Issues / Problems Presented</h2>
    <div class="part-body">{!! $log->part1_subjective_objective ?: '<p>-</p>' !!}</div>

    <h2>Part 2: Assessment of Issues / Problems</h2>
    <div class="part-body">{!! $log->part2_assessment ?: '<p>-</p>' !!}</div>

    <h2>Part 3: Treatment Planning and Action</h2>
    <div class="part-body">{!! $log->part3_treatment_plan ?: '<p>-</p>' !!}</div>

    <h2>Part 4: Evaluation of Counseling Session</h2>
    <div class="part-body">{!! $log->part4_evaluation ?: '<p>-</p>' !!}</div>

    <table class="summary">
        <tr>
            <td class="label">Date and Time</td>
            <td>{{ optional($log->signed_at)->format('d M Y, H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Signature</td>
            <td>
                <div class="signature-box">
                    @if ($log->signature)
                        <img src="{{ $log->signature }}" alt="Signature">
                    @else
                        Not signed
                    @endif
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
