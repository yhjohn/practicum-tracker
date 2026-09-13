<?php

use App\Livewire\PublicIntakeForm;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/intake/{intake}', PublicIntakeForm::class)
    ->middleware('signed')
    ->name('intake.show');
