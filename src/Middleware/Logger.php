<?php

namespace MgpLabs\SmartAdmin\Middleware;

use Closure;
use MgpLabs\SmartAdmin\Services\StringService;
use MgpLabs\SmartAdmin\Modules\Log\RequestLogger as Log;

class Logger
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
        if(StringService::existInString('/api/', $request->url())){
            Log::dispatchRequest($request)->logRequest();
        }

        return $next($request);
    }
}
