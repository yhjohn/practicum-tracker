<?php

namespace App\Filament\Resources\MentalStatusExaminations\Schemas;

use App\Filament\Schemas\Mse\MseSummary;
use Filament\Schemas\Schema;

class MentalStatusExaminationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(MseSummary::sections());
    }
}
