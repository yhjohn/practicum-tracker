<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0; text-align: center; }
        h2 { font-size: 11px; text-transform: uppercase; background: #f3f4f6; padding: 4px 6px; margin: 14px 0 6px; }
        h3 { font-size: 12px; margin: 16px 0 0; }
        p { line-height: 1.5; margin: 6px 0; }
        .subtitle { text-align: center; color: #4b5563; margin: 4px 0 16px; }
        ol { padding-left: 18px; }
        ol li { margin-bottom: 6px; }
        table.summary { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.summary td { padding: 6px 0; vertical-align: top; }
        table.summary td.label { width: 160px; font-weight: bold; }
        .signature-box { border: 1px solid #d1d5db; padding: 8px; margin-top: 6px; }
        .signature-box img { height: 80px; }
    </style>
</head>
<body>
    <h1>Group Informed Consent Form</h1>
    <p class="subtitle">{{ $groupConsent->group->name }} &middot; Open University Malaysia</p>

    @include('partials.group-consent-terms')

    <h3>Client</h3>
    <table class="summary">
        <tr>
            <td class="label">Full name</td>
            <td>{{ $groupConsent->full_name }}</td>
        </tr>
        <tr>
            <td class="label">Date of birth</td>
            <td>{{ optional($groupConsent->dob)->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Acknowledgement</td>
            <td>{{ $groupConsent->agree ? 'Agreed' : 'Not agreed' }}</td>
        </tr>
        <tr>
            <td class="label">Date signed</td>
            <td>{{ optional($groupConsent->signature_date)->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Signature</td>
            <td>
                <div class="signature-box">
                    @if ($groupConsent->signature)
                        <img src="{{ $groupConsent->signature }}" alt="Signature">
                    @else
                        Not signed
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if ($groupConsent->guardian_name || $groupConsent->guardian_signature)
        <h3>Parent / Guardian (for clients under the age of 18)</h3>
        <table class="summary">
            <tr>
                <td class="label">Full name</td>
                <td>{{ $groupConsent->guardian_name ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Relationship to client</td>
                <td>{{ $groupConsent->guardian_relationship ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Date</td>
                <td>{{ optional($groupConsent->signature_date)->format('d M Y') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Signature</td>
                <td>
                    <div class="signature-box">
                        @if ($groupConsent->guardian_signature)
                            <img src="{{ $groupConsent->guardian_signature }}" alt="Guardian signature">
                        @else
                            Not signed
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    @endif

    <h3>Pre-Service Counsellor</h3>
    <table class="summary">
        <tr>
            <td class="label">Full name</td>
            <td>Joleen Poh Ee Bing</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td>{{ optional($groupConsent->signature_date)->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Signature</td>
            <td>@include('pdf.signature')</td>
        </tr>
    </table>
</body>
</html>
