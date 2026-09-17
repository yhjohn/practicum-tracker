<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Group;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
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
        ]);

        URL::forceScheme('https');

        if (! request()->is('admin*')) {
            FilamentColor::register([
                'primary' => Color::Blue,
            ]);
        }
    }
}
