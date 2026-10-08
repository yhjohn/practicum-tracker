<?php

use App\Livewire\PublicClientPortal;
use App\Livewire\PublicConsentForm;
use App\Livewire\PublicGroupConsentForm;
use App\Livewire\PublicIntakeForm;
use App\Livewire\PublicIntakeFormV2;
use App\Models\Consent;
use App\Models\GroupConsent;
use App\Models\Intake;
use App\Models\IntakeV2;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/portal/{token}', PublicClientPortal::class)
    ->middleware('signed')
    ->name('client.portal');

Route::get('/intake/{intake}', function (Intake $intake) {
    if (! session("client_portal.{$intake->client_id}") || now()->gt(session("client_portal.{$intake->client_id}"))) {
        return redirect()->to(URL::temporarySignedRoute('client.portal', $intake->client->public_access_expires_at, ['token' => $intake->client->public_access_token]));
    }

    return app()->call(PublicIntakeForm::class.'@__invoke', ['intake' => $intake]);
})->middleware('signed')->name('intake.show');

Route::get('/intake-v2/{intake}', function (IntakeV2 $intake) {
    if (! session("client_portal.{$intake->client_id}") || now()->gt(session("client_portal.{$intake->client_id}"))) {
        return redirect()->to(URL::temporarySignedRoute('client.portal', $intake->client->public_access_expires_at, ['token' => $intake->client->public_access_token]));
    }

    return app()->call(PublicIntakeFormV2::class.'@__invoke', ['intake' => $intake]);
})->middleware('signed')->name('intake-v2.show');

Route::get('/consent/{consent}', function (Consent $consent) {
    if (! session("client_portal.{$consent->client_id}") || now()->gt(session("client_portal.{$consent->client_id}"))) {
        return redirect()->to(URL::temporarySignedRoute('client.portal', $consent->client->public_access_expires_at, ['token' => $consent->client->public_access_token]));
    }

    return app()->call(PublicConsentForm::class.'@__invoke', ['consent' => $consent]);
})->middleware('signed')->name('consent.show');

Route::get('/group-consent/{groupConsent}', function (GroupConsent $groupConsent) {
    if (! session("client_portal.{$groupConsent->client_id}") || now()->gt(session("client_portal.{$groupConsent->client_id}"))) {
        return redirect()->to(URL::temporarySignedRoute('client.portal', $groupConsent->client->public_access_expires_at, ['token' => $groupConsent->client->public_access_token]));
    }

    return app()->call(PublicGroupConsentForm::class.'@__invoke', ['groupConsent' => $groupConsent]);
})->middleware('signed')->name('group-consent.show');
