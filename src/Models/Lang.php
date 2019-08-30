<?php
namespace MgpLabs\SmartAdmin\Models;

class Lang extends SmartAdminBaseModel
{
    protected $fillable = [
        'code',
        'term'
    ];

    protected $rules = [
        'code' => 'required|max:2|unique:langs,code',
        'term' => 'required'
    ];

    /**
     * Get All Available locales
     *
     * @param int $lowercase
     * @return mixed
     */
    public static function getLocales($lowercase = 0){
        $data = self::pluck('code', 'id')->toArray();

        if($lowercase){
            foreach($data as &$_lang){
                $_lang = strtolower($_lang);
            }
        }

        return $data;
    }

    /**
     * Get Locale id
     *
     * @param $locale
     * @return mixed
     */
    public static function getLocaleId($locale){
        $locale_id = self::where('code', $locale)->first();
        return $locale_id ? $locale_id['id'] : null;
    }
}
