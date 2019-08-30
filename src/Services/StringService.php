<?php

namespace MgpLabs\SmartAdmin\Services;

use Illuminate\Support\Str;

class StringService extends BaseService
{
    /**
     * Convert text to camelCase by separator ["/"]
     *
     * @param $string
     * @param string $separator
     * @param boolean $capitalise_first
     * @return mixed
     */
    public static function toCamelCase($string, $capitalise_first = true, $separator = '_'){
        $str = str_replace(' ', '', ucwords(str_replace($separator, ' ', $string)));
        if(!$capitalise_first){
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }

    /**
     * Cut $needle from $from string
     *
     * @param $needle
     * @param $from
     * @return string
     */
    public static function cutFromString($needle, $from){
        return str_replace($needle, '', $from);
    }

    /**
     * Check existence of string [array|string]
     *
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function existInString($needle, $haystack){
        if(is_array($needle)){
            foreach($needle as $_needle){
                if(strpos($haystack, $_needle) !== false){
                    return true;
                }
            }
        }else{
            if(strpos($haystack, $needle) !== false){
                return true;
            }

        }
        return false;
    }

    /**
     * Table name to class name
     *
     * @param $tbl_name
     * @return string
     */
    public static function toClassName($tbl_name){
        return Str::studly(Str::singular($tbl_name));
    }

    /**
     * Return uppercase string after given char
     *
     * @param $char
     * @param $haystack
     * @return string
     */
    public static function upperAfterChar($char, $haystack){
        $needle = explode($char, $haystack);
        return $needle[0] . $char . strtoupper($needle[1]);
    }

    /**
     * Compare first char of string with given char
     *
     * @param string $char
     * @param string $string
     * @return bool
     */
    public static function checkFirstChar($char, $string){
        return mb_substr($string, 0, 1, 'utf-8') === $char;
    }

    /**
     * Make storage directory name
     *
     * @param $model_name
     * @return string
     */
    public static function dirName($model_name){
        return strtolower($model_name) . 's';
    }
}