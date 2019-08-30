<?php

namespace MgpLabs\SmartAdmin\Services;


use Illuminate\Http\JsonResponse;
use MgpLabs\SmartAdmin\Modules\Log\RequestLogger;

class ApiResponseService extends BaseService
{
    /**
     * Validation Json Errors
     *
     * @param $errors
     * @return JsonResponse
     */
    public static function validationError($errors){
        RequestLogger::logResponseError('VALIDATION_ERROR', 422, $errors);

        return response()->json(['err_type' => 'VALIDATION_ERROR', 'errors' => $errors], 422);
    }

    /**
     * Json Error response
     *
     * @param $type
     * @param $code
     * @param null $errors
     * @return JsonResponse
     */
    public static function error($type, $code, $errors = null){
        RequestLogger::logResponseError($type, $code, $errors);

        $json = ['err_type' => $type];

        if($errors){
            $json['errors'] = $errors;
        }

        return response()->json($json, $code);
    }

    /**
     * Returns error as String
     *
     * @param $str
     * @param int $code
     * @return JsonResponse
     */
    public static function stringError($str, $code = 400){
        RequestLogger::logResponseError('STRING_ERROR', $code, $str);

        return response()->json($str, $code);
    }

    /**
     * Authentication Errors
     *
     * @param $err_type
     * @return JsonResponse
     */
    public static function authError($err_type){
        RequestLogger::logResponseError($err_type, 401);

        return response()->json(['err_type' => $err_type], 401);
    }

    /**
     * Json response with custom body
     *
     * @param $msg
     * @param $data
     * @return JsonResponse
     */
    public static function success($data = [], $msg = ''){
        $response['success'] = true;

        if($msg){
            $response['message'] = $msg;
        }

        if(config('app.debug')){
            $response['request_time'] = microtime(true) - $_REQUEST['mt'];
            $response['memory_usage'] = memory_get_usage() / 1024 / 1024  . ' MB';
            $response['memory_usage_real'] = memory_get_usage(true) / 1024 / 1024  . ' MB';
            $response['peak_memory_usage'] = memory_get_peak_usage() / 1024 / 1024  . ' MB';
            $response['peak_memory_usage_real'] = memory_get_peak_usage(true) / 1024 / 1024  . ' MB';
        }

        $response['data'] = $data;

        RequestLogger::logResponseSuccess($response);

        return response()->json($response, 200);
    }

    /**
     * Return response with custom body
     *
     * @param $body
     * @return JsonResponse
     */
    public static function successCustom($body){
        RequestLogger::logResponseSuccess($body);

        if(config('app.debug')){
            $body['request_time'] = microtime(true) - $_REQUEST['mt'];
            $body['memory_usage'] = memory_get_usage() / 1024 / 1024  . ' MB';
            $body['memory_usage_real'] = memory_get_usage(true) / 1024 / 1024  . ' MB';
            $body['peak_memory_usage'] = memory_get_peak_usage() / 1024 / 1024  . ' MB';
            $body['peak_memory_usage_real'] = memory_get_peak_usage(true) / 1024 / 1024  . ' MB';
        }

        return response()->json($body, 200);
    }

    /**
     * Force dumper
     *
     * @param $params
     */
    public static function forceJson($params){
        header('Content-type: application/json');
        echo response()->json([$params], 500);die;
    }

    /**
     * Incorrect Params
     *
     * @return JsonResponse
     */
    public static function incorrectParams(){
        RequestLogger::logResponseError('INCORRECT_PARAMS', 422);

        return response()->json(['err_type' => 'INCORRECT_PARAMS'], 422);
    }

    public static function incorrectFile($message){
        RequestLogger::logResponseError('INCORRECT_FILE', 422, $message);

        return response()->json(['err_type' => 'INCORRECT_FILE', 'message' => $message], 422);
    }

    /**
     * Uncatchable Error
     *
     * @param $msg
     * @return JsonResponse
     */
    public static function unCatchableError($msg = null){
        RequestLogger::logResponseError('UNCATCHABLE_ERROR', 422, $msg);

        $data = ['err_type' => 'UNCATCHABLE_ERROR'];

        if($msg){
            $data['msg'] = $msg;
        }
        return response()->json($data, 422);
    }
}