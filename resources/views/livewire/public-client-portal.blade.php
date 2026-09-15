<div class="mx-auto max-w-4xl px-4 py-10">
    <x-filament-actions::modals />

    @if ($step === 'expired')
        <div class="rounded-xl border border-red-200 bg-red-50 p-8 text-center shadow-sm">
            <h1 class="text-xl font-semibold text-red-900">This portal link has expired</h1>
            <p class="mt-2 text-sm text-red-700">Please contact your counsellor for a new link.</p>
        </div>
    @elseif ($step === 'code')
        <div class="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
            <h1 class="text-xl font-semibold text-gray-900">Enter your access code</h1>
            <p class="mt-2 text-sm text-gray-600">Enter the code provided to access your forms.</p>

            <form wire:submit="verifyAccessCode" class="mt-6 space-y-4">
                <div>
                    <label for="codeInput" class="block text-sm font-medium text-gray-700">Access code</label>
                    <input id="codeInput" type="text" inputmode="numeric" autocomplete="off" wire:model="codeInput" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    @error('codeInput') <p class="mt-1 py-3 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                    Continue
                </button>
            </form>
        </div>
    @else
        <div class="space-y-6">
            <header class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-wide text-primary-600">Client portal</p>
                        <h1 class="mt-1 text-2xl font-semibold text-gray-900">{{ $client->name }}</h1>
                    </div>
                    <button type="button" wire:click="startOver" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Sign out
                    </button>
                </div>
            </header>

            <div class="grid gap-4 md:grid-cols-2">
                

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Consent form</h2>
                        @if (($consent?->status ?? \App\Enums\ConsentStatus::Pending) === \App\Enums\ConsentStatus::Submitted)
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">✓ Signed</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">! Pending</span>
                        @endif
                    </div>

                    @if (($consent?->status ?? \App\Enums\ConsentStatus::Pending) === \App\Enums\ConsentStatus::Submitted)
                        <p class="mt-4 text-sm text-gray-600">Your consent form has been signed and submitted.</p>
                    @else
                        <p class="mt-4 text-sm text-gray-600">Your consent form is still pending.</p>
                        <a href="{{ $consentUrl }}" class="mt-4 inline-flex rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                            Open consent form
                        </a>
                    @endif
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Intake form</h2>
                        @if (($intake?->status ?? \App\Enums\IntakeStatus::Pending) === \App\Enums\IntakeStatus::Submitted)
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">✓ Complete</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">! Pending</span>
                        @endif
                    </div>

                    @if (($intake?->status ?? \App\Enums\IntakeStatus::Pending) === \App\Enums\IntakeStatus::Submitted)
                        <p class="mt-4 text-sm text-gray-600">Your intake form has been submitted.</p>
                    @else
                        <p class="mt-4 text-sm text-gray-600">Your intake form is still open for completion.</p>
                        <a href="{{ $intakeUrl }}" class="mt-4 inline-flex rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                            Open intake form
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
