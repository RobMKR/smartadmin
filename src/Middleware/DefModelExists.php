<?php

namespace MgpLabs\SmartAdmin\Middleware;

use Closure;
use MgpLabs\SmartAdmin\Services\ApiResponseService;
use MgpLabs\SmartAdmin\Services\StringService;

class DefModelExists
{
    /**
     * Models Namespace
     */
    const __NAMESPACE__ = 'App\\Models\\';

    /**
     * Vendor Models Namespace
     */
    const __VENDOR__NAMESPACE__ = 'MgpLabs\\SmartAdmin\\Models\\';

    /**
     * Model Name
     *
     * @var
     */
    private $model_name;

    /**
     * Checks model is default or not
     * You find default models in config/smartadmin.php => defaultmodels
     *
     * @return bool
     */
    private function modelIsDefault(){
        $default_models = config('smartadmin.defaultmodels');

        foreach($default_models as $_model){
            if(!isset($_model['value']))
                continue;

            if($this->model_name === StringService::toCamelCase($_model['value'])){
                return true;
            }
        }
        return false;
    }

    /**
     * Checks model is vendor or not
     * You find vendor models in config/smartadmin.php => vendormodels
     *
     * @return bool
     */
    private function modelIsVendor(){
        $vendor_models = config('smartadmin.vendormodels');

        foreach($vendor_models as $_model){
            if(!isset($_model['value']))
                continue;

            if($this->model_name === StringService::toCamelCase($_model['value'])){
                return true;
            }
        }
        return false;
    }

    /**
     * Check Class Existence
     *
     * @param $model_name
     * @return bool
     */
    private function classExists($model_name){
        if(array_key_exists($model_name, config('smartadmin.vendormodels'))){
            $namespace = self::__VENDOR__NAMESPACE__;
        }else{
            $namespace = self::__NAMESPACE__;
        }

        return class_exists($namespace . $this->model_name);
    }

    /**
     * Handle an incoming request.
     * Checks Requested Default Model Existence
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->model_name = StringService::toCamelCase($request->route('model_name'));

        if(! $this->classExists($request->route('model_name')))
            return ApiResponseService::error('MODEL_NOT_FOUND', 404);

        if($this->modelIsDefault() || $this->modelIsVendor()){
            return $next($request);
        }

        return ApiResponseService::error('MODEL_NOT_FOUND', 404);
    }
}
