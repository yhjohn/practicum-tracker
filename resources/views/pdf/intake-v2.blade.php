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
        table.fields td.label { width: 260px; font-weight: bold; }
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
        <tr><td class="label">Age</td><td>{{ $joined($intake->age) }}</td></tr>
        <tr><td class="label">Phone</td><td>{{ $joined($intake->phone) }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $joined($intake->email) }}</td></tr>
        <tr><td class="label">Address</td><td>{!! nl2br(e($joined($intake->address))) !!}</td></tr>
        <tr><td class="label">Relationship status</td><td>{{ $joined($intake->marital) }}</td></tr>
        <tr><td class="label">Occupation</td><td>{{ $joined($intake->occupation) }}</td></tr>
        <tr><td class="label">Sex</td><td>{{ $joined($intake->sex) }}</td></tr>
        <tr><td class="label">Emergency contact's full name</td><td>{{ $joined($intake->emergency_name) }}</td></tr>
        <tr><td class="label">Relationship to you</td><td>{{ $joined($intake->emergency_relationship) }}</td></tr>
        <tr><td class="label">Emergency contact phone number</td><td>{{ $joined($intake->emergency_phone) }}</td></tr>
    </table>

    <h2>Education, Career & Personal Strengths</h2>
    <table class="fields">
        <tr><td class="label">Highest level of education completed</td><td>{{ $joined($intake->education_level) }}</td></tr>
        <tr><td class="label">Education (other, specified)</td><td>{{ $joined($intake->education_level_other) }}</td></tr>
        <tr><td class="label">Job satisfaction</td><td>{{ $joined($intake->job_satisfaction) }}</td></tr>
        <tr><td class="label">Activities enjoyed</td><td>{{ $joined($intake->enjoyable_activities) }}</td></tr>
        <tr><td class="label">Activities (other, specified)</td><td>{{ $joined($intake->enjoyable_activities_other) }}</td></tr>
        <tr><td class="label">Personal qualities or strengths</td><td>{{ $joined($intake->personal_strengths) }}</td></tr>
        <tr><td class="label">Strengths (other, specified)</td><td>{{ $joined($intake->personal_strengths_other) }}</td></tr>
        <tr><td class="label">Activities or resources that help coping</td><td>{{ $joined($intake->coping_resources) }}</td></tr>
        <tr><td class="label">Coping resources (other, specified)</td><td>{{ $joined($intake->coping_resources_other) }}</td></tr>
        <tr><td class="label">Strengths or support to build on</td><td>{{ $joined($intake->strengths_to_build) }}</td></tr>
    </table>

    <h2>Family & Social Background</h2>
    <table class="fields">
        <tr><td class="label">Family relationships</td><td>{{ $joined($intake->family_relationships) }}</td></tr>
        <tr><td class="label">Family relationships (other, specified)</td><td>{{ $joined($intake->family_relationships_other) }}</td></tr>
        <tr><td class="label">Childhood or family experiences</td><td>{{ $joined($intake->childhood_experiences) }}</td></tr>
        <tr><td class="label">Childhood experiences (other, specified)</td><td>{{ $joined($intake->childhood_experiences_other) }}</td></tr>
        <tr><td class="label">Support received from family or friends</td><td>{{ $joined($intake->family_support_received) }}</td></tr>
        <tr><td class="label">Religious, spiritual or personal beliefs</td><td>{{ $joined($intake->beliefs) }}</td></tr>
        <tr><td class="label">Beliefs (other, specified)</td><td>{{ $joined($intake->beliefs_other) }}</td></tr>
        <tr><td class="label">Role of religion/spirituality in upbringing</td><td>{{ $joined($intake->spiritual_upbringing_role) }}</td></tr>
        <tr><td class="label">Substances currently used</td><td>{{ $joined($intake->substances_used) }}</td></tr>
        <tr><td class="label">Substances (other, specified)</td><td>{{ $joined($intake->substances_used_other) }}</td></tr>
        <tr><td class="label">Frequency of use</td><td>{{ $joined($intake->substance_use_frequency) }}</td></tr>
        <tr><td class="label">Frequency (other, specified)</td><td>{{ $joined($intake->substance_use_frequency_other) }}</td></tr>
        <tr><td class="label">Anything else about background, family, beliefs or support</td><td>{{ $joined($intake->background_notes) }}</td></tr>
    </table>

    <h2>Counselling / Medical History</h2>
    <table class="fields">
        <tr><td class="label">Previously received counselling or therapy</td><td>{{ $joined($intake->previous_counselling) }}</td></tr>
        <tr><td class="label">When / main reason for seeking support</td><td>{{ $joined($intake->previous_counselling_when) }}</td></tr>
        <tr><td class="label">Previous mental health diagnosis</td><td>{{ $joined($intake->mental_health_diagnosis) }}</td></tr>
        <tr><td class="label">Diagnosis specified</td><td>{{ $joined($intake->mental_health_diagnosis_details) }}</td></tr>
        <tr><td class="label">Psychiatric services, hospitalisation or medication</td><td>{{ $joined($intake->psychiatric_services) }}</td></tr>
        <tr><td class="label">What was helpful or unhelpful</td><td>{{ $joined($intake->previous_support_feedback) }}</td></tr>
        <tr><td class="label">Emotions and mood recently</td><td>{{ $joined($intake->mood_recent) }}</td></tr>
        <tr><td class="label">Mood (other, specified)</td><td>{{ $joined($intake->mood_recent_other) }}</td></tr>
        <tr><td class="label">Thoughts about harming self or others</td><td>{{ $joined($intake->harm_thoughts) }}</td></tr>
        <tr><td class="label">Support needed / response</td><td>{{ $joined($intake->harm_thoughts_response) }}</td></tr>
        <tr><td class="label">Current problems or stresses</td><td>{{ $joined($intake->current_stressors) }}</td></tr>
        <tr><td class="label">Stressors (other, specified)</td><td>{{ $joined($intake->current_stressors_other) }}</td></tr>
        <tr><td class="label">How much these concerns affect daily life</td><td>{{ $joined($intake->current_stressors_impact) }}</td></tr>
        <tr><td class="label">Main concern seeking counselling for</td><td>{{ $joined($intake->main_concern) }}</td></tr>
        <tr><td class="label">What you would like to achieve</td><td>{{ $joined($intake->counselling_goals) }}</td></tr>
        <tr><td class="label">Goals (other, specified)</td><td>{{ $joined($intake->counselling_goals_other) }}</td></tr>
        <tr><td class="label">Anything else you would like to share</td><td>{{ $joined($intake->anything_else) }}</td></tr>
    </table>

    <h2>Confidentiality Acknowledgement</h2>
    <p>My counsellor has explained the purpose of this intake form and the confidentiality of information shared, including its limits. I understand that I may ask questions about these matters.</p>
    <table class="fields">
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
        <tr><td class="label">Date signed</td><td>{{ optional($intake->signature_date)->format('d M Y') ?? '—' }}</td></tr>
        <tr><td class="label">Submitted at</td><td>{{ optional($intake->submitted_at)->format('d M Y, H:i') ?? '—' }}</td></tr>
    </table>
</body>
</html>
