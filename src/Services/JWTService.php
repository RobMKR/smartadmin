<?php
namespace MgpLabs\SmartAdmin\Services;

use JWTAuth; /* Loaded from config/app aliases */
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
class JWTService extends BaseService
{
    /**
     * User ID
     *
     * @var null
     */
    protected static $user_id = null;

    /**
     * Get Locale
     *
     * @return mixed
     */
    public static function getLocale(){
        try {
            \JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return config('app.locale');
        } catch (JWTException $e) {
            return config('app.locale');
        }

        $payload_array = JWTAuth::parseToken()->getPayload()->toArray();
        return isset($payload_array['locale']) ? $payload_array['locale'] : config('app.locale');
    }

    /**
     * Get User Id
     *
     * @return null
     */
    public static function getUserId(){
        if(self::$user_id === null){
            $user = JWTAuth::parseToken()->authenticate();
            self::$user_id = ($user !== null) ? $user->id : null ;
        }

        return self::$user_id;
    }
}