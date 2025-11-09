<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1);
        $availableLocales = config('app.available_locales');

        if (in_array($locale, $availableLocales)) {
            App::setLocale($locale);
            session(['locale' => $locale]);
        } elseif (session()->has('locale')) {
            App::setLocale(session('locale'));
        } else {
            $browserLanguage = $request->getPreferredLanguage(['pl', 'en']);
            $detectedLocale = $browserLanguage === 'pl' ? 'pl' : 'en';
            App::setLocale($detectedLocale);
            session(['locale' => $detectedLocale]);
        }

        URL::defaults(['locale' => App::getLocale()]);

        return $next($request);
    }
}
