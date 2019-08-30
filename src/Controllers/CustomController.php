<?php

namespace MgpLabs\SmartAdmin\Controllers;

use MgpLabs\SmartAdmin\Models\Permission;
use MgpLabs\SmartAdmin\Services\ApiResponseService;
use MgpLabs\SmartAdmin\Services\StringService;
use Illuminate\Http\Request;
use MgpLabs\SmartAdmin\Models\Translation;
use MgpLabs\SmartAdmin\Models\Lang;
use MgpLabs\SmartAdmin\Models\UserConfig;
use MgpLabs\SmartAdmin\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Routing\Router as Route;
use MgpLabs\SmartAdmin\Patterns\ModelFactory;

class CustomController extends BaseController
{
    /**
     * Generates Permissions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generatePermissions(Route $router){
        $permissions = [
            [
                'resource' => '/*',
                'path' => '/*'
            ],
            [
                'resource' => '/*/*',
                'path' => '/*'
            ],
        ];

        foreach($router->getRoutes() as $_route){
            // Case When route hasn't any middleware
            if(count($_route->middleware()) === 1 && $_route->middleware()[0] === 'api'){
                continue;
            }

            // Web middleware
            if(count($_route->middleware()) === 1 && $_route->middleware()[0] === 'web'){
                continue;
            }

            // Case for AUTH only
            if(count($_route->middleware()) === 2 && $_route->middleware()[1] === 'jwt.auth'){
                continue;
            }

            // Case for default models
            if(in_array('model_exists', $_route->middleware())){
                continue;
            }

            foreach($_route->methods() as $_method){
                $group = str_replace('api/', '', $_route->uri());
                switch ($_method){
                    case 'GET':
                        $permissions[] = [
                            'resource' => $group .  '/r',
                            'path' => $group
                        ];
                        break;
                    case 'POST':
                        $permissions[] = [
                            'resource' => $group .  '/c',
                            'path' => $group
                        ];
                        $permissions[] = [
                            'resource' => $group .  '/u',
                            'path' => $group
                        ];
                        break;
                    case 'DELETE':
                        $permissions[] = [
                            'resource' => $group .  '/d',
                            'path' => $group
                        ];
                        break;
                    case 'HEAD':
                        continue;
                }

                if(!in_array(['resource' => $group .  '/*', 'path' => $group], $permissions)){
                    $permissions[] = [
                        'resource' => $group .  '/*',
                        'path' => $group
                    ];
                }
            }
        }

        // Default Models
        $default_models = config('smartadmin.defaultmodels');

        // Vendor Models
        $vendor_models = config('smartadmin.vendormodels');

        // Merge default and vendor
        $merged = array_merge($default_models, $vendor_models);

        // Add Default models to ACL
        foreach( $merged as $_model_name => $_model){

            if(array_key_exists($_model_name, $default_models)){
                $namespace = 'App\Models\\';
            }else{
                $namespace = 'MgpLabs\SmartAdmin\Models\\';
            }

            $object_name = $namespace . StringService::toCamelCase($_model['value']);
            $object = new $object_name;

            foreach($object->getCrudOptions() as $_type => $_allow){
                if($_allow == false){
                    continue;
                }

                if(!in_array(['resource' => 'default/' . $_model['value'] .  '/*', 'path' => 'default/' . $_model['value']], $permissions)){
                    $permissions[] = [
                        'resource' => 'default/' . $_model['value'] .  '/*',
                        'path' => 'default/' . $_model['value']
                    ];
                }

                $permissions[] = [
                    'resource' => 'default/' . $_model['value'] . '/' . $_type,
                    'path' => 'default/' . $_model['value']
                ];
            }
        }
        
        foreach($permissions as $_permission){
            Permission::firstOrCreate($_permission);
        }

        return ApiResponseService::success('L_PERMISSIONS_GENERATED');
    }

    /**
     * Get Fields of given model [By Model Name]
     *
     * @param $model_name
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultModelFields($model_name){
        $model_name = self::__MODEL_NAMESPACE__ . StringService::toCamelCase($model_name);

        $Model = new $model_name();

        $response['data'] = $Model->getFields();
        $response['metadata']['crud'] = $Model->getCrudOptions();

        return ApiResponseService::successCustom($response);
    }

    /**
     * Save temporary translations to translations table, if term not exist in any of language codes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noTranslation(Request $request){
        $terms = $request->has('terms') ? $request->input('terms') : null;

        if($terms){
            $Langs = Lang::pluck('code', 'id');

            foreach($terms as $_term){
                $translation = Translation::where('term', $_term)->first();

                if(!$translation){
                    $translation = (new Translation)->create([
                        'term' => $_term,
                    ]);

                    foreach($Langs as $_locale){
                        $translation->{'text:' . $_locale} = $_term;
                    }
                    $translation->save();
                }
            }
        }

        return ApiResponseService::successCustom(['message' => 'Terms Saved', 'data' => []]);
    }

    /**
     * Save User configs as JSON
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUserConfigs(Request $request){
        if(!$request->has('configs')){
            return ApiResponseService::incorrectParams();
        }

        $user_id = User::getAuthenticatedUser()['id'];

        if($configs = UserConfig::createOrUpdate($user_id, $request->input('configs'))){
            return ApiResponseService::successCustom(['data' => $configs, 'message' => 'Configs Saved']);
        }

        return ApiResponseService::unCatchableError('Uncatchable error in CustomController@saveUserConfigs');

    }

    /**
     * Get All Translations
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTranslations(Request $request){
        $locale = $request->has('locale') ? strtoupper($request->input('locale')) : config('app.locale');

        $translations = (new Translation)->join('translation_translations as t', 't.translation_id', '=', 'translations.id')
            ->where('locale', $locale)
            ->select('translations.term', 't.text')
            ->get();

        return ApiResponseService::success($translations);
    }

    /**
     * Get Default Models
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultModels(){
        $default_models = array_merge(config('smartadmin.defaultmodels'), config('smartadmin.vendormodels'));

        foreach($default_models as &$model){
            $model['model'] = StringService::toCamelCase($model['value']);
        }

        return ApiResponseService::success($default_models);
    }

    /**
     * Get All Default Model Fields
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllDefaultModelFields(){

        $default_models = array_merge(config('smartadmin.defaultmodels'), config('smartadmin.vendormodels'));

        if(!config('app.debug') && Cache::has('all-default-fields')){
            $result = Cache::get('all-default-fields');
        }else{
            $result = [];

            foreach($default_models as $_model){
                $model = ModelFactory::createModel($_model['value']);
                $result[$_model['value']] = $model->getFields();
            }

            Cache::forever('all-default-fields', $result);
        }

        return ApiResponseService::success($result);
    }

    /**
     * Get All Languages
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLangs(){
        return ApiResponseService::success((new Lang)->get());
    }
}
