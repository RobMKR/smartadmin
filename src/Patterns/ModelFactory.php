<?php
namespace MgpLabs\SmartAdmin\Patterns;

use MgpLabs\SmartAdmin\Services\StringService;

class ModelFactory
{
    /**
     * Models Namespace constant
     */
    const __MODEL_NAMESPACE__ = 'App\\Models\\';

    /**
     * Vendor Model Namespace
     */
    const __VENDOR_MODEL_NAMESPACE__ = 'MgpLabs\\SmartAdmin\\Models\\';

    /**
     * Model Name
     *
     * @param $model_name
     * @return mixed
     */
    public static function createModel($model_name)
    {
        if(array_key_exists($model_name, config('smartadmin.vendormodels'))){
            $model_name = self::__VENDOR_MODEL_NAMESPACE__ . StringService::toCamelCase($model_name);
        }else{
            $model_name = self::__MODEL_NAMESPACE__ . StringService::toCamelCase($model_name);
        }

        return new $model_name;
    }
}