<?php
namespace MgpLabs\SmartAdmin\Models;

class RolePermission extends SmartAdminBaseModel
{

    protected $destinations = [
        'role_id' => [
            'model' => 'permission',
            'key' => 'permission_id',
            'fields' => [
                'resource'
            ]
        ],
        'permission_id' => [
            'model' => 'role',
            'key' => 'role_id',
            'fields' => [
                'alias',
            ],
        ]
    ];

    protected $fillable = [
        'role_id',
        'permission_id'
    ];

    protected $rules = [
        'role_id' => 'required|integer|exists:roles,id',
        'permission_id' => 'required|integer|exists:permissions,id'
    ];

    public function role(){
        return parent::belongsTo($this->modelName('Role'));
    }

    public function permission(){
        return parent::belongsTo($this->modelName('Permission'));
    }
}
