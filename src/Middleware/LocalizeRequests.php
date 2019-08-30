<?php

namespace MgpLabs\SmartAdmin\Middleware;

use Closure;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use MgpLabs\SmartAdmin\Services\JWTService;
use Illuminate\Support\Facades\App;

class LocalizeRequests
{
    /**
     * Token Validity
     *
     * @var
     */
    protected $check = true;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->has('token') || $request->hasHeader('Authorization')){

            try {
                \JWTAuth::parseToken()->authenticate();
            } catch (TokenExpiredException $e) {
                $this->check = false;
            } catch (JWTException $e) {
                $this->check = false;
            }

            if($this->check){
                App::setLocale(JWTService::getLocale());
            }

        }
        
        return $next($request);
    }
}
