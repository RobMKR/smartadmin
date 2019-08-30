<?php
namespace MgpLabs\SmartAdmin\Models;

use \Dimsav\Translatable\Translatable;

class Translation extends SmartAdminBaseModel
{
    use Translatable;

    public $translatedAttributes = ['text'];

    public $relation_column = 'translation_id';

    protected $fillable = [
        'term',
    ];

    protected $non_editable = [
        'term'
    ];

    protected $rules = [
        'term' => 'required|max:64',
    ];
	
	protected $cache = false;

    /**
     * Merge Arrays
     * Extended from parent -> BaseModel
     * Need to reversed, because this method used to merge translatable and normal fields [translatable first]
     * We need this merge only in reversed sort [translatable last]
     *
     * @param $array1
     * @param $array2
     * @return array
     */
    protected function merge($array1, $array2)
    {
        return parent::merge($array2, $array1);
    }
}
