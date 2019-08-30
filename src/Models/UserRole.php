<?php
namespace MgpLabs\SmartAdmin\Models;

class UserRole extends SmartAdminBaseModel
{
    protected $destinations = [
        'role_id' => [
            'model' => 'user',
            'key' => 'user_id',
            'fields' => [
                'name',
                'surname'
            ]
        ],
        'user_id' => [
            'model' => 'role',
            'key' => 'role_id',
            'fields' => [
                'alias',
            ],
        ]
    ];

    protected $fillable = [
        'user_id',
        'role_id'
    ];

    protected $rules = [
        'user_id' => 'required|integer|exists:users,id',
        'role_id' => 'required|integer|exists:roles,id',
    ];

    public function role(){
        return parent::belongsTo($this->modelName('Role'));
    }

    public function user(){
        return parent::belongsTo($this->modelName('User'));
    }
}
