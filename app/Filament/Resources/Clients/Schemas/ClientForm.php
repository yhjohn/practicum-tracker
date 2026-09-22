<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Filament\Schemas\Intakes\IntakeWizard;
use App\Filament\Schemas\Intakes\IntakeWizardV2;
use App\Models\Client;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('client_code')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel()
                    ->prefix('+60 ')
                    ->required(),
                TextInput::make('emergency_contact')
                    ->default(null),
                TextInput::make('emergency_contact_phone')
                    ->tel()
                    ->prefix('+60 ')
                    ->default(null),

                static::intakeSection('Intake form (V1)', 'intake', IntakeWizard::baseSteps()),
                static::intakeSection('Intake form (V2)', 'intakeV2', IntakeWizardV2::baseSteps()),
            ]);
    }

    /**
     * @param  array<Step>  $steps
     */
    protected static function intakeSection(string $title, string $relationship, array $steps): Section
    {
        return Section::make($title)
            ->visible(fn (?Client $record): bool => $record?->{$relationship} !== null)
            ->collapsible()
            // ->collapsed()
            ->schema([
                Group::make()
                    ->relationship($relationship)
                    ->schema(array_map(
                        fn (Step $step): Section => Section::make($step->getLabel())
                            ->schema($step->getDefaultChildComponents())
                            ->collapsible()
                            ->collapsed(),
                        $steps,
                    )),
            ])
            ->columnSpanFull();
    }
}
