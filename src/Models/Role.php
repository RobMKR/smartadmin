<?php
namespace MgpLabs\SmartAdmin\Models;

use \Dimsav\Translatable\Translatable;

class Role extends SmartAdminBaseModel
{
    use Translatable;

    public function __construct(array $attributes = [])
    {
        // Fix Empty fillable bug
        // When fillable is empty array, cannot create multiselect
        // filling it with one empty attribute
        if(empty($this->fillable)){
            $this->fillable = [sha1($this->getTable() . $_REQUEST['mt'])];
        }

        parent::__construct($attributes);
    }

    protected $rels = [
        'permissions' => [
            'model' => 'Permission'
        ]
    ];

    public $relation_column = 'role_id';

    protected $multiselect = [
        'role_permission' => [
            'model' => 'role_permission',
            'as' => 'role_id',
            'fk' => 'permission_id',
            'rel_name' => 'permissions'
        ]
    ];

    public $translatedAttributes = ['alias'];

    public function permissions(){
        return $this->belongsToMany($this->modelName('Permission'), 'role_permissions', 'role_id')->withTimestamps();
    }

    public function users(){
        return $this->belongsToMany($this->modelName('Role'), 'user_roles', 'role_id')->withTimestamps();
    }
}
