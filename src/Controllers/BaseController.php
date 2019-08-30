<?php
namespace MgpLabs\SmartAdmin\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use MgpLabs\SmartAdmin\Models\SmartAdminBaseModel;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Models Namespace
     */
    const __MODEL_NAMESPACE__ = 'App\\Models\\';

    const __VENDOR_MODEL_NAMESPACE__ = 'MgpLabs\\SmartAdmin\\Models\\';

    /**
     * Check request has all given params or not
     *
     * @param Request $request
     * @param $params
     * @return bool
     */
    protected function has(Request $request, $params){
        foreach($params as $_param){
            if(! $request->has($_param)){
                return false;
            }
        }

        return true;
    }

    /**
     * Confirm Given Entities for model
     *
     * @param SmartAdminBaseModel $model
     * @param $entities [[id, confirmed], [id, confirmed], ....]
     * @return mixed
     */
    protected function confirm(SmartAdminBaseModel $model, $entities){
        $to_save = [
            'confirmed' => [],
            'not_confirmed' => [],
        ];

        foreach($entities as $_entity){
            if($_entity['confirmed'] === 'confirmed'){
                $to_save['confirmed'][] = $_entity['id'];
            }else{
                $to_save['not_confirmed'][] = $_entity['id'];
            }
        }

        $model->whereIn('id', $to_save['not_confirmed'])->update(['confirmed' => 0]);

        $confirmed_count = $model->whereIn('id', $to_save['confirmed'])->update(['confirmed' => 1]);

        return $confirmed_count;
    }

}