<?php
namespace MgpLabs\SmartAdmin;

use Illuminate\Support\ServiceProvider;
use MgpLabs\SmartAdmin\Modules\Validator\Validator;
use Illuminate\Support\Facades\Validator as BaseValidator;
use DB;

class SmartAdminServiceProvider extends ServiceProvider{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        BaseValidator::extend('rtf', function ($attribute, $value, $parameters, $validator) {
            return true;
        });

        BaseValidator::extend('translatable', function ($attribute, $value, $parameters, $validator) {
            // Explode value to field name and locale, e.g. [name:ru] to [0 => 'name', 1 => 'ru']
            list($field['name'], $field['locale']) = explode(':', $attribute);
            // Prepare db query
            $query = DB::table($parameters[0])->where($field['name'], $value)->where($parameters[1], $field['locale']);

            // Check if [$parameter[2]] and [$parameter[2]] is passed, add id and relation to query
            // $parameter[2] must be TranslationRelation and  $parameter[3] must be relation id
            if(isset($parameters[2]) && isset($parameters[3])){
                $query->where($parameters[2], '!=', $parameters[3]);
            }

            $check = $query->first();

            return $check ? false : true;
        });

        BaseValidator::resolver(function($translator, $data, $rules, $messages)
        {
            return new Validator($translator, $data, $rules, $messages);
        });

        $this->publishes([__DIR__.'/config/smartadmin.php' => config_path('smartadmin.php')]);

        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
    }
}