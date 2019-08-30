<?php

namespace MgpLabs\SmartAdmin\Services;


use Illuminate\Http\Request;

class UrlService extends BaseService
{
    /**
     * Base Url with "/" in end
     *
     * @return string
     */
    public static function base(){
        return url('/') . '/';
    }

    /**
     * Get uri from url
     *
     * @param $url
     * @return mixed
     */
    public static function getUri($url){
        return str_replace(self::base(), '', $url);
    }

    /**
     * Get CRUD type
     *
     * @param $request
     * @return string
     */
    public static function getCrudType(Request $request){
        $uri = str_replace('api/', '', $request->path());

        switch ($request->method()){
            case 'GET' :
                $type = 'r';
                break;
            case 'POST' :
                if($request->has('id')){
                    $type = 'u';
                }else{
                    $type = 'c';
                }
                break;
            case 'DELETE' :
                $type = 'd';
                break;
            default:
                return false;
        }
        return $uri . '/' .$type;
    }
}