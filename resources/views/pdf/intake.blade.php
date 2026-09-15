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
        table.fields { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.fields td { padding: 4px 0; vertical-align: top; }
        table.fields td.label { width: 220px; font-weight: bold; }
        .signature-box { border: 1px solid #d1d5db; padding: 8px; margin-top: 6px; }
        .signature-box img { height: 80px; }
    </style>
</head>
<body>
    <h1>Adult Counselling Intake Form</h1>
    <p class="subtitle">{{ $intake->name }}</p>

    @php
        $joined = fn (mixed $value): string => filled($value) ? (is_array($value) ? implode(', ', $value) : $value) : '—';
    @endphp

    <h2>Personal Information</h2>
    <table class="fields">
        <tr><td class="label">Full name</td><td>{{ $joined($intake->name) }}</td></tr>
        <tr><td class="label">Date of birth</td><td>{{ optional($intake->dob)->format('d M Y') ?? '—' }}</td></tr>
        <tr><td class="label">Age</td><td>{{ $joined($intake->age) }}</td></tr>
        <tr><td class="label">Phone</td><td>{{ $joined($intake->phone) }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $joined($intake->email) }}</td></tr>
        <tr><td class="label">Occupation</td><td>{{ $joined($intake->occupation) }}</td></tr>
        <tr><td class="label">Sex</td><td>{{ $joined($intake->sex) }}</td></tr>
        <tr><td class="label">Emergency contact's full name</td><td>{{ $joined($intake->emergency_name) }}</td></tr>
        <tr><td class="label">Relationship to you</td><td>{{ $joined($intake->emergency_relationship) }}</td></tr>
        <tr><td class="label">Emergency contact phone number</td><td>{{ $joined($intake->emergency_phone) }}</td></tr>
    </table>

    <h2>Marital / Relationship Status</h2>
    <table class="fields">
        <tr><td class="label">Marital status</td><td>{{ $joined($intake->marital) }}</td></tr>
        <tr><td class="label">Length of current relationship</td><td>{{ $joined($intake->relationship_length) }}</td></tr>
        <tr><td class="label">Number of previous marriages</td><td>{{ $joined($intake->marriages) }}</td></tr>
    </table>

    <h2>Education</h2>
    <table class="fields">
        <tr><td class="label">Highest level of education completed</td><td>{{ $joined($intake->education) }}</td></tr>
        <tr><td class="label">Currently enrolled in</td><td>{{ $joined($intake->education_current) }}</td></tr>
        <tr><td class="label">Relevant details or circumstances</td><td>{{ $joined($intake->education_details) }}</td></tr>
    </table>

    <h2>Career / Employment</h2>
    <table class="fields">
        <tr><td class="label">Current employment / job title</td><td>{{ $joined($intake->job_title) }}</td></tr>
        <tr><td class="label">Employment history and relevant circumstances</td><td>{{ $joined($intake->employment_history) }}</td></tr>
        <tr><td class="label">Job satisfaction (1–5) and why</td><td>{{ $joined($intake->job_satisfaction) }}</td></tr>
    </table>

    <h2>Personal Strengths and Supports</h2>
    <table class="fields">
        <tr><td class="label">Activities you enjoy or feel successful doing</td><td>{{ $joined($intake->strength_activities) }}</td></tr>
        <tr><td class="label">Personal qualities or strengths</td><td>{{ $joined($intake->strengths) }}</td></tr>
        <tr><td class="label">Strengths others might say you have</td><td>{{ $joined($intake->others_strengths) }}</td></tr>
        <tr><td class="label">Who or what supports you</td><td>{{ $joined($intake->supports) }}</td></tr>
    </table>

    <h2>Traumatic Experiences or Significant Losses</h2>
    <table class="fields">
        <tr><td class="label">Experiences ticked</td><td>{{ $joined($intake->trauma) }}</td></tr>
        <tr><td class="label">Anything you would like to add</td><td>{{ $joined($intake->trauma_details) }}</td></tr>
    </table>

    <h2>Family History</h2>
    <table class="fields">
        <tr><td class="label">How would you describe your family?</td><td>{{ $joined($intake->family_description) }}</td></tr>
        <tr><td class="label">Childhood or family experiences</td><td>{{ $joined($intake->childhood_experience) }}</td></tr>
        <tr><td class="label">Support received from family or friends</td><td>{{ $joined($intake->family_support) }}</td></tr>
    </table>

    <h2>Counselling / Medical History</h2>
    <table class="fields">
        <tr><td class="label">Previous counsellor or therapist</td><td>{{ $joined($intake->counselling_history) }}</td></tr>
        <tr><td class="label">Previous mental health diagnosis</td><td>{{ $joined($intake->diagnosis) }}</td></tr>
        <tr><td class="label">What was helpful or unhelpful</td><td>{{ $joined($intake->therapy_experience) }}</td></tr>
        <tr><td class="label">Psychiatric services, hospitalisation, or medication</td><td>{{ $joined($intake->medical_history) }}</td></tr>
    </table>

    <h2>Substance Use</h2>
    <table class="fields">
        <tr><td class="label">Alcohol, tobacco, or other substance use</td><td>{{ $joined($intake->substance_use) }}</td></tr>
        <tr><td class="label">Support or treatment received</td><td>{{ $joined($intake->substance_treatment) }}</td></tr>
    </table>

    <h2>Legal Issues</h2>
    <table class="fields">
        <tr><td class="label">Current or past legal issues</td><td>{{ $joined($intake->legal_issues) }}</td></tr>
        <tr><td class="label">How these issues have affected you</td><td>{{ $joined($intake->legal_impact) }}</td></tr>
    </table>

    <h2>Emotional / Mental Wellbeing</h2>
    <table class="fields">
        <tr><td class="label">Emotions and mood recently</td><td>{{ $joined($intake->emotions) }}</td></tr>
        <tr><td class="label">Thoughts about harming yourself or someone else</td><td>{{ $joined($intake->safety) }}</td></tr>
        <tr><td class="label">Who you could turn to for support</td><td>{{ $joined($intake->support_system) }}</td></tr>
        <tr><td class="label">Significant problems or stresses</td><td>{{ $joined($intake->current_stress) }}</td></tr>
        <tr><td class="label">Memory or concentration concerns</td><td>{{ $joined($intake->memory) }}</td></tr>
    </table>

    <h2>Spirituality / Beliefs</h2>
    <table class="fields">
        <tr><td class="label">Belief system or spiritual practice</td><td>{{ $joined($intake->belief_system) }}</td></tr>
        <tr><td class="label">Religion or spiritual practice in upbringing</td><td>{{ $joined($intake->spiritual_upbringing) }}</td></tr>
    </table>

    <h2>Reasons for Seeking Counselling</h2>
    <table class="fields">
        <tr><td class="label">Concern for which you are seeking counselling</td><td>{{ $joined($intake->seeking_reason) }}</td></tr>
        <tr><td class="label">When you first noticed this concern</td><td>{{ $joined($intake->first_occurred) }}</td></tr>
        <tr><td class="label">What is most concerning right now</td><td>{{ $joined($intake->main_concern) }}</td></tr>
        <tr><td class="label">Concerns expressed by family or friends</td><td>{{ $joined($intake->family_concern) }}</td></tr>
        <tr><td class="label">What you would like to achieve</td><td>{{ $joined($intake->counselling_outcome) }}</td></tr>
        <tr><td class="label">Anything else you would like to share</td><td>{{ $joined($intake->anything_else) }}</td></tr>
    </table>

    <h2>Confidentiality Acknowledgement and Signature</h2>
    <p>My counsellor has explained the purpose of this intake form and the confidentiality of information shared, including its limits. I understand that I may ask questions about these matters.</p>
    <table class="fields">
        <tr><td class="label">Date signed</td><td>{{ optional($intake->signature_date)->format('d M Y') ?? '—' }}</td></tr>
        <tr><td class="label">Submitted at</td><td>{{ optional($intake->submitted_at)->format('d M Y, H:i') ?? '—' }}</td></tr>
        <tr>
            <td class="label">Signature</td>
            <td>
                <div class="signature-box">
                    @if ($intake->signature)
                        <img src="{{ $intake->signature }}" alt="Signature">
                    @else
                        Not signed
                    @endif
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
