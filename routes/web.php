<?php

use App\Livewire\PublicConsentForm;
use App\Livewire\PublicIntakeForm;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/intake/{intake}', PublicIntakeForm::class)
    ->middleware('signed')
    ->name('intake.show');

Route::get('/consent/{consent}', PublicConsentForm::class)
    ->middleware('signed')
    ->name('consent.show');
