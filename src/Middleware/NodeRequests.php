<?php

namespace MgpLabs\SmartAdmin\Middleware;

use Closure;
use MgpLabs\SmartAdmin\Services\ApiResponseService;

class NodeRequests
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
        if($request->header('x-node-token') === config('app.node_key')){
            return $next($request);
        }

        return ApiResponseService::authError('X_NODE_AUTH_FAILED');
    }
}
