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
            'legal_documents_last_updated' => config('app.legal_documents_last_updated'),
            'translations' => $this->getTranslations(),
        ];
    }

    /**
     * Get translations for the current locale.
     *
     * @return array<string, mixed>
     */
    protected function getTranslations(): array
    {
        $locale = app()->getLocale();
        $translations = [];

        $jsonFile = lang_path("{$locale}.json");
        if (file_exists($jsonFile)) {
            $translations = json_decode(file_get_contents($jsonFile), true) ?? [];
        }

        $legalFile = lang_path("{$locale}/legal.php");
        if (file_exists($legalFile)) {
            $translations['legal'] = require $legalFile;
        }

        $authFile = lang_path("{$locale}/auth.php");
        if (file_exists($authFile)) {
            $translations['auth'] = require $authFile;
        }

        $commonFile = lang_path("{$locale}/common.php");
        if (file_exists($commonFile)) {
            $translations['common'] = require $commonFile;
        }

        return $translations;
    }
}
