@php($consent = $intake->signature ? null : $intake->client?->consent)
<div class="signature-box">
    @if ($intake->signature)
        <img src="{{ $intake->signature }}" alt="Signature">
    @elseif ($consent?->signature)
        <img src="{{ $consent->signature }}" alt="Signature">
        <div>Signature taken from consent form signed {{ optional($consent->signature_date)->format('d M Y') ?? '—' }}</div>
    @else
        Not signed
    @endif
</div>
