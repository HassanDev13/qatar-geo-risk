<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
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
        // Force English locale only
        // Language switching is currently disabled
        // To re-enable multi-language support, uncomment the code below

        $locale = "en";

        /* Multi-language support (currently disabled)
        // Available locales
        $availableLocales = ['en', 'ar'];

        // Priority order for locale detection:
        // 1. Query parameter (?lang=ar)
        // 2. Session (user preference)
        // 3. Browser Accept-Language header
        // 4. Default (en)

        $locale = null;

        // Check query parameter
        if ($request->has('lang') && in_array($request->get('lang'), $availableLocales)) {
            $locale = $request->get('lang');
            Session::put('locale', $locale);
        }

        // Check session
        if (!$locale && Session::has('locale')) {
            $locale = Session::get('locale');
        }

        // Check browser language
        if (!$locale) {
            $browserLang = $request->getPreferredLanguage($availableLocales);
            if ($browserLang) {
                $locale = $browserLang;
            }
        }

        // Fallback to default
        if (!$locale || !in_array($locale, $availableLocales)) {
            $locale = config('app.locale', 'en');
        }
        */

        // Set application locale
        App::setLocale($locale);

        return $next($request);
    }
}
