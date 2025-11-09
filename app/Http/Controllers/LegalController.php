<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function privacyPolicy(): Response
    {
        App::setLocale('en');

        return Inertia::render('Legal/PrivacyPolicy', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function privacyPolicyPl(): Response
    {
        App::setLocale('pl');

        return Inertia::render('Legal/PrivacyPolicy', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function termsOfService(): Response
    {
        App::setLocale('en');

        return Inertia::render('Legal/TermsOfService', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function termsOfServicePl(): Response
    {
        App::setLocale('pl');

        return Inertia::render('Legal/TermsOfService', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function legalDisclaimer(): Response
    {
        App::setLocale('en');

        return Inertia::render('Legal/LegalDisclaimer', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function legalDisclaimerPl(): Response
    {
        App::setLocale('pl');

        return Inertia::render('Legal/LegalDisclaimer', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}
