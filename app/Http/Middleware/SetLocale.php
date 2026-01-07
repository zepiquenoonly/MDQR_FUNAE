<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // ordem de prioridade: user -> session -> cookie -> config
        $locale = null;

        if ($request->user()?->locale) {
            $locale = $request->user()->locale;
        } elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } elseif ($request->cookie('locale')) {
            $locale = $request->cookie('locale');
        } else {
            $locale = config('locales.default', config('app.locale'));
        }

        // define o locale global
        app()->setLocale($locale);

        // também define timezone do utilizador se disponível
        if ($request->user()?->timezone) {
            date_default_timezone_set($request->user()->timezone);
        } elseif ($request->session()->has('timezone')) {
            date_default_timezone_set($request->session()->get('timezone'));
        }

        return $next($request);
    }
}