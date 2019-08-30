<?php

namespace MgpLabs\SmartAdmin\Middleware;

use Closure;

class Cors
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
        /*
         *  Check instance of BinaryFileResponse
         * If is_a return true, it means that request object
         * is an instance of BinaryFileResponse class of Symfony
         * BinaryFileResponse have not header() method,
         * And we don't need add alternative headers to response
         * so we just skip to next request without passing any headers to response
         * This step needed to avoid [BinaryFileResponse::header() method not found] exception
         */

        $next_request = $next($request);

        if(is_a($next_request, 'Symfony\Component\HttpFoundation\BinaryFileResponse')){
            return $next_request;
        }

        return $next_request
            ->header('Access-Control-Allow-Origin', config('smartadmin.cors.allow-origin'))
            ->header('Access-Control-Allow-Methods', config('smartadmin.cors.allow-methods'))
            ->header('Access-Control-Allow-Headers', config('smartadmin.cors.allow-headers'));
    }
}
