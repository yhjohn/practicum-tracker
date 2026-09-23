<x-filament-widgets::widget>
    <x-filament::card>
        <h2 class="text-base font-semibold text-gray-950">
            Practicum Progress
        </h2>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full whitespace-nowrap text-start text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs font-medium text-gray-500">
                        <th class="py-2 pe-3 text-start">Component</th>
                        <th class="px-3 py-2 text-end">Target (hrs)</th>
                        <th class="px-3 py-2 text-end">Completed (hrs)</th>
                        <th class="px-3 py-2 text-end">Remaining (hrs)</th>
                        <th class="px-3 py-2 text-end">% Complete</th>
                        <th class="ps-3 py-2 text-end">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($rows as $row)
                        <tr @class(['font-semibold' => str_starts_with($row['label'], 'TOTAL') || $row['label'] === 'GRAND TOTAL'])>
                            <td class="py-2 pe-3">{{ $row['label'] }}</td>
                            <td class="px-3 py-2 text-end">{{ $row['target'] }}</td>
                            <td class="px-3 py-2 text-end">{{ rtrim(rtrim(number_format($row['completed'], 2), '0'), '.') ?: '0' }}</td>
                            <td class="px-3 py-2 text-end">{{ rtrim(rtrim(number_format($row['remaining'], 2), '0'), '.') ?: '0' }}</td>
                            <td class="px-3 py-2 text-end">{{ $row['percent'] === null ? 'N/A' : $row['percent'].'%' }}</td>
                            <td class="ps-3 py-2 text-end">
                                <x-filament::badge :color="match ($row['status']) {
                                    'Complete' => 'success',
                                    'In Progress' => 'warning',
                                    'Below Target' => 'danger',
                                    default => 'gray',
                                }">
                                    {{ $row['status'] }}
                                </x-filament::badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
