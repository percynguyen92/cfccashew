<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['en']);
        $defaultLocale = config('app.locale', 'en');

        $locale = $request->getPreferredLanguage($supportedLocales);
        $sessionLocale = $request->session()->get('locale');
        $cookieLocale = $request->cookie('app_locale');

        if (is_string($cookieLocale) && in_array($cookieLocale, $supportedLocales, true)) {
            $locale = $cookieLocale;
        }

        if (is_string($sessionLocale) && in_array($sessionLocale, $supportedLocales, true)) {
            $locale = $sessionLocale;
        }

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = $defaultLocale;
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);
        Cookie::queue(cookie('app_locale', $locale, 60 * 24 * 365));

        return $next($request);
    }
}
