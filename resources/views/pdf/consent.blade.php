<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0; text-align: center; }
        h2 { font-size: 11px; text-transform: uppercase; background: #f3f4f6; padding: 4px 6px; margin: 14px 0 6px; }
        p { line-height: 1.5; margin: 6px 0; }
        .subtitle { text-align: center; color: #4b5563; margin: 4px 0 16px; }
        ol { padding-left: 18px; }
        ol li { margin-bottom: 6px; }
        table.summary { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.summary td { padding: 6px 0; vertical-align: top; }
        table.summary td.label { width: 160px; font-weight: bold; }
        .signature-box { border: 1px solid #d1d5db; padding: 8px; margin-top: 6px; }
        .signature-box img { height: 80px; }
    </style>
</head>
<body>
    <h1>Informed Consent Form</h1>
    <p class="subtitle">Open University Malaysia &middot; Master of Counselling</p>

    @include('partials.consent-terms')

    <table class="summary">
        <tr>
            <td class="label">Full name</td>
            <td>{{ $consent->full_name }}</td>
        </tr>
        <tr>
            <td class="label">Acknowledgement</td>
            <td>{{ $consent->agree ? 'Agreed' : 'Not agreed' }}</td>
        </tr>
        <tr>
            <td class="label">Date signed</td>
            <td>{{ optional($consent->signature_date)->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Submitted at</td>
            <td>{{ optional($consent->submitted_at)->format('d M Y, H:i') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Signature</td>
            <td>
                <div class="signature-box">
                    @if ($consent->signature)
                        <img src="{{ $consent->signature }}" alt="Signature">
                    @else
                        Not signed
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">Client name</td>
            <td>{{ $consent->full_name }}</td>
        </tr>
        <tr>
            <td class="label">Counsellor signature</td>
            <td>
                <div class="signature-box">
                    @include('pdf.signature')
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">Counsellor name</td>
            <td>Joleen Poh Ee Bing</td>
        </tr>
        <tr>
            <td class="label">Counsellor date signed</td>
            <td>{{ optional($consent->signature_date)->format('d M Y') ?? '-' }}</td>
        </tr>
    </table>
</body>
</html>
