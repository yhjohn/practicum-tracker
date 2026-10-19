<?php

namespace App\Filament\Resources\Clients\Resources\SessionPlans;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Resources\SessionPlans\Pages\CreateSessionPlan;
use App\Filament\Resources\Clients\Resources\SessionPlans\Pages\EditSessionPlan;
use App\Filament\Resources\Clients\Resources\SessionPlans\Schemas\SessionPlanForm;
use App\Filament\Resources\Clients\Resources\SessionPlans\Tables\SessionPlansTable;
use App\Models\SessionPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SessionPlanResource extends Resource
{
    protected static ?string $model = SessionPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Notepad;

    protected static ?string $parentResource = ClientResource::class;

    protected static ?string $recordTitleAttribute = 'session_number';

    public static function form(Schema $schema): Schema
    {
        return SessionPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SessionPlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateSessionPlan::route('/create'),
            'edit' => EditSessionPlan::route('/{record}/edit'),
        ];
    }
}
