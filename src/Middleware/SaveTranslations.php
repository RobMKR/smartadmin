<?php

namespace MgpLabs\SmartAdmin\Middleware;

use Closure;
use MgpLabs\SmartAdmin\Services\TranslationService;

class SaveTranslations
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        TranslationService::saveNotFoundedTranslations();

        return $response;
    }
}