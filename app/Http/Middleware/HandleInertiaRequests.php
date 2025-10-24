<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'locale' => app()->getLocale(),
            'available_locales' => config('app.available_locales'),
            'translations' => $this->getTranslations(),
        ];
    }

    /**
     * Get translations for the current locale.
     *
     * @return array<string, string>
     */
    protected function getTranslations(): array
    {
        $locale = app()->getLocale();
        $translationFile = lang_path("{$locale}.json");

        if (! file_exists($translationFile)) {
            return [];
        }

        return json_decode(file_get_contents($translationFile), true) ?? [];
    }
}
