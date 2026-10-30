<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use App\Filament\Resources\IndividualCounsellingLogs\Widgets\IndividualCounsellingLogsOverview;
use App\Jobs\ImportIndividualCounsellingLog;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListIndividualCounsellingLogs extends ListRecords
{
    protected static string $resource = IndividualCounsellingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importPdf')
                ->label('Import from PDF')
                ->icon(Phosphor::FileArrowUp)
                ->color('gray')
                ->modalDescription('Restore a report from an exported individual counselling report PDF. Importing the same client and session again updates that report.')
                ->schema([
                    FileUpload::make('pdf')
                        ->label('Report PDF')
                        ->disk('local')
                        ->directory('report-imports')
                        ->visibility('private')
                        ->acceptedFileTypes(['application/pdf'])
                        ->required(),
                    Select::make('client_id')
                        ->label('Client')
                        ->options(fn (): array => Client::query()->orderBy('client_code')->get()->pluck('name_with_code', 'id')->all())
                        ->searchable()
                        ->required()
                        ->helperText('The client code written in the report is changed to this client\'s code.'),
                ])
                ->action(function (array $data): void {
                    ImportIndividualCounsellingLog::dispatch($data['pdf'], Client::findOrFail($data['client_id']));

                    Notification::make()
                        ->success()
                        ->title('Importing report')
                        ->body('It will appear in the list in about a minute. Check it against the PDF before relying on it.')
                        ->send();
                }),
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            IndividualCounsellingLogsOverview::class,
        ];
    }
}
