<?php

namespace MgpLabs\SmartAdmin\Modules\Log;

use MgpLabs\SmartAdmin\Services\StringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestLogger
{
    /**
     * Route is exception or not
     *
     * @bool
     */
    protected static $excepted = false;

    /**
     * Log Id
     *
     * @var
     */
    protected static $log_id;

    /**
     * Start time
     *
     * @var
     */
    protected static $start_time;

    /**
     * Request Method
     *
     * @string
     */
    protected static $method;

    /**
     * Request Uri
     *
     * @string
     */
    protected static $uri;

    /**
     * Request Data
     *
     * @array
     */
    protected static $data;

    /**
     * Has Auth Token or not
     *
     * @bool
     */
    protected static $has_auth;

    /**
     * Create Log Message
     *
     * @return string
     */
    protected function createRequestMessage(){
        $message['log_id'] = self::$log_id;
        $message['method'] = self::$method;
        $message['uri'] = self::$uri;
        $message['has_auth_token'] = self::$has_auth;
        $message['data'] = self::$data;

        return json_encode($message);
    }

    /**
     * Checks current route is exception or not
     * If it is in exceptions array, turn off log for it
     *
     * @return bool
     */
    protected static function isException(){
        $exceptions = config('smartadmin.logs.request.except');

        foreach ($exceptions as $_route){
            if(StringService::existInString($_route, self::$uri)){
                return true;
            }
        }

        return false;
    }

    /**
     * Save Log message depended on given log type
     *
     * @param string $function
     * @return void | bool
     */
    public function logRequest($function = 'info'){
        if(self::$excepted)
            return false;

        $message = $this->createRequestMessage();

        switch ($function){
            case 'info':
                Log::info($message);
                break;
        }
    }

    /**
     * Log Response Errors
     *
     * @param $type
     * @param $code
     * @param $errors
     */
    public static function logResponseError($type, $code, $errors = []){
        $message['log_id'] = self::$log_id;
        $message['error_type'] = $type;
        $message['error_code'] = $code;
        $message['errors'] = $errors;
        $message['request_duration'] = (microtime(1) - self::$start_time);

        if(!self::$excepted)
            Log::error(json_encode($message) . PHP_EOL);
    }

    /**
     * Log Response Success
     *
     * @param $body
     */
    public static function logResponseSuccess($body = []){
        $message['log_id'] = self::$log_id;
        $message['code'] = 200;
        $message['body'] = $body;
        $message['request_duration'] = (microtime(1) - self::$start_time);

        if(!self::$excepted)
            Log::info(json_encode($message) . PHP_EOL);
    }

    /**
     * Dispatch request object
     *
     * @param Request $request
     * @return self
     */
    public static function dispatchRequest(Request $request){
        self::$start_time = microtime(1);
        self::$log_id = rand(1000000, 9999999);
        self::$method = $request->method();
        self::$uri = $request->getUri();
        self::$has_auth = $request->hasHeader('Authorization') || $request->has('token');
        self::$excepted = self::isException();

        $data = $request->all();
        if(isset($data['password'])){
            $data['password'] = 'PROTECTED_FIELD_SET';
        }

        if(isset($data['token'])){
            unset($data['token']);
        }

        self::$data = $data;

        return new static;
    }
}