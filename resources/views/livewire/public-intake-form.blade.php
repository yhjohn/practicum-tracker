<div class="mx-auto max-w-7xl px-4 py-10">
    <x-filament-actions::modals />

    @if ($step === 'submitted')
        <div class="rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            <h1 class="text-xl font-semibold text-gray-900">This intake form has already been submitted</h1>
            <p class="mt-2 text-sm text-gray-600">Thank you, we've already received your responses. Please contact your counsellor if you need to make changes.</p>
        </div>
    @elseif ($step === 'code')
        <div class="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
            <h1 class="text-xl font-semibold text-gray-900">Enter your access code</h1>
            <p class="mt-2 text-sm text-gray-600">Enter the 6-digit access code your counsellor gave you to continue.</p>

            <form wire:submit="verifyAccessCode" class="mt-6 space-y-4">
                <div>
                    <label for="codeInput" class="block text-sm font-medium text-gray-700">Access code</label>
                    <input id="codeInput" type="text" inputmode="numeric" autocomplete="off" wire:model="codeInput" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    @error('codeInput') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                    Continue
                </button>
            </form>
        </div>
    @else
        <header class="mb-6 text-center">
            <h1 class="text-2xl font-semibold text-gray-900">Adult Counselling Intake Form</h1>
            <p class="mt-2 text-sm text-gray-600">Please complete the sections you feel comfortable answering. You may leave a response blank or discuss it with your counsellor.</p>
        </header>

        <form wire:submit="submit">
            {{ $this->form }}
        </form>

        <div class="mt-4 flex justify-end">
            <button type="button" wire:click="saveProgress" wire:loading.attr="disabled" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Save progress
            </button>
        </div>
    @endif
</div>
