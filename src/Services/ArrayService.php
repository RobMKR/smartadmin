<?php
namespace MgpLabs\SmartAdmin\Services;


class ArrayService extends BaseService
{

    /**
     * Get Given key value pairs from given array
     *
     * @param $keys
     * @param $array
     * @return array
     */
    public static function getByKeys($keys, &$array){
        $return = [];

        foreach($keys as $_key){
            if(isset($array[$_key])){
                $return[$_key] = $array[$_key];
            }
        }

        return $return;
    }

    /**
     * Returns true if all given keys exist in array
     *
     * @param array $keys
     * @param array $array
     * @return bool
     */
    public static function issetKeys(array $keys, array $array){
        foreach($keys as $_key){
            if(!isset($array[$_key])){
                return false;
            }
        }

        return true;
    }

    /**
     * Give array of any depth, take one depth array in dot notation
     * EXAMPLE::::
     * [
     *      'key1' => [
     *          'key1_1 => '',
     *          'key2_1 => 'SOmETHIng'
     *      ],
     *      'key2' => [],
     * ]
     *
     * WILL BE TRANSFORMED TO
     *
     * [
     *      'key1',
     *      'key1.key1_1,
     *      'key1.key2_1,
     *      'key2,
     * ]
     *
     * EXAMPLE::::
     *
     * @param $keys [reference]
     * @param array $array
     * @param null $delimeter
     * @return array
     */
    public static function arrayKeysToDots(&$keys, array $array, $delimeter = null){
        foreach($array as $_key => $_array){
            if($delimeter){
                $string = $delimeter . '.' . $_key;
            }else{
                $string = $_key;
            }

            $keys[] = $string;

            if(is_array($_array) && !empty($_array)){
                self::arrayKeysToDots($keys, $_array, $string);
            }
        }

        return $keys;
    }
}