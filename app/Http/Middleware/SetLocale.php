<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1);
        $availableLocales = config('app.available_locales');

        if (in_array($locale, $availableLocales)) {
            App::setLocale($locale);
            session(['locale' => $locale]);
        } elseif (session()->has('locale')) {
            App::setLocale(session('locale'));
        }

        URL::defaults(['locale' => App::getLocale()]);

        return $next($request);
    }
}
