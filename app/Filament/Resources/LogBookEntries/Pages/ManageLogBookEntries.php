<?php

namespace App\Filament\Resources\LogBookEntries\Pages;

use App\Exports\LogBookExport;
use App\Filament\Resources\LogBookEntries\LogBookEntryResource;
use App\Filament\Widgets\PracticumProgressOverview;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Maatwebsite\Excel\Facades\Excel;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ManageLogBookEntries extends ManageRecords
{
    protected static string $resource = LogBookEntryResource::class;

    /**
     * @return array<int, class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            PracticumProgressOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportXlsx')
                ->label('Export XLSX')
                ->icon(Phosphor::FileXls)
                ->color('gray')
                ->action(function () {
                    // ponytail: the XLSX template (18 weekly sheets, styles, merges) needs
                    // more than PHP's default 256M to load and fill; raise per-request if
                    // exports start failing again with more weeks or entries.
                    ini_set('memory_limit', '512M');

                    $export = new LogBookExport;

                    foreach ($export->warnings() as $warning) {
                        Notification::make()->warning()->title('Log book export')->body($warning)->persistent()->send();
                    }

                    return Excel::download($export, 'practicum-log-book.xlsx');
                }),
            CreateAction::make()
                ->label('Add activity')
                ->modalHeading('Add activity'),
        ];
    }
}
