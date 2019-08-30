<?php

namespace MgpLabs\SmartAdmin\Middleware;

use Closure;
use MgpLabs\SmartAdmin\Models\User;
use MgpLabs\SmartAdmin\Services\UrlService;
use MgpLabs\SmartAdmin\Services\ACLService;
use MgpLabs\SmartAdmin\Services\ApiResponseService;

class VerifyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next){
        // Get User From Token
        $user = User::getAuthenticatedUser();

        // Get Request Type ['c', 'r', 'u', 'd']
        $uri_full = UrlService::getCrudType($request);

        // If Not Correct Request Method
        if($uri_full === false){
            return ApiResponseService::error('METHOD_NOT_ALLOWED', 405);
        }

        $access = ACLService::checkAccess($uri_full, $user);

        // Check user has access or not
        if($access['status']){
            return $next($request);
        }

        // Return 403 if, user has'nt access
        return ApiResponseService::error('ACCESS_DENIED', 403, ['param' => $access['param']]);
    }
}
