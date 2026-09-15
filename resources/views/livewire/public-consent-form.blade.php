<div class="mx-auto max-w-4xl px-4 py-10">
    @if ($step === 'submitted')
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            <h1 class="text-xl font-semibold text-gray-900">This consent form has already been signed</h1>
            <p class="mt-2 text-sm text-gray-600">Thank you, we've already received your signed consent. Please contact your counsellor if you have questions.</p>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
            <header class="mb-6 text-center">
                <h1 class="text-2xl font-semibold text-gray-900">Informed Consent Form</h1>
                <p class="mt-2 text-sm text-gray-600">Open University Malaysia &middot; Master of Counselling</p>
            </header>

            <div class="space-y-4 text-sm leading-relaxed text-gray-700">
                @include('partials.consent-terms')
            </div>

            <form wire:submit="submit" class="mt-8 rounded-lg border border-gray-200 bg-gray-50 p-6">
                {{ $this->form }}

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                        Sign &amp; Submit
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
