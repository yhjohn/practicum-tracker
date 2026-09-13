<div class="mx-auto max-w-4xl px-4 py-10">
    <x-filament-actions::modals />

    @if ($intake->status === \App\Enums\IntakeStatus::Submitted)
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            <h1 class="text-xl font-semibold text-gray-900">This intake form has already been submitted</h1>
            <p class="mt-2 text-sm text-gray-600">Thank you, we've already received your responses. Please contact your counsellor if you need to make changes.</p>
        </div>
    @else
        <header class="mb-6 text-center">
            <h1 class="text-2xl font-semibold text-gray-900">Adult Counselling Intake Form</h1>
            <p class="mt-2 text-sm text-gray-600">Please complete the sections you feel comfortable answering. You may leave a response blank or discuss it with your counsellor.</p>
        </header>

        <form wire:submit="submit">
            {{ $this->form }}
        </form>
    @endif
</div>
