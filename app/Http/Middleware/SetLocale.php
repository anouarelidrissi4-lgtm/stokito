<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['fr', 'en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', 'fr');

        if (! in_array($locale, self::SUPPORTED)) {
            $locale = 'fr';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
