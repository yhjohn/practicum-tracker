<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Group;
use App\Models\GroupCounsellingLog;
use App\Models\IndividualCounsellingLog;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'client' => Client::class,
            'group' => Group::class,
            'individual_counselling_log' => IndividualCounsellingLog::class,
            'group_counselling_log' => GroupCounsellingLog::class,
        ]);

        URL::forceScheme('https');

        Table::configureUsing(fn (Table $table): Table => $table->defaultPaginationPageOption(50));

        if (! request()->is('admin*')) {
            FilamentColor::register([
                'primary' => Color::Blue,
            ]);
        }
    }
}
