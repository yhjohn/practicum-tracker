<?php

namespace App\Filament\Widgets;

use App\Models\LogBookEntry;
use Filament\Widgets\Widget;

class PracticumProgressOverview extends Widget
{
    protected string $view = 'filament.widgets.practicum-progress-overview';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'rows' => LogBookEntry::progressSummary(),
        ];
    }
}
