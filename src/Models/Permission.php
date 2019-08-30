<?php
namespace MgpLabs\SmartAdmin\Models;

class Permission extends SmartAdminBaseModel
{
    protected $fillable = [
        'resource',
        'path'
    ];

    protected $rules = [
        'resource' => 'required|unique:permissions,resource'
    ];

    public function roles()
    {
        return $this->belongsToMany($this->modelName('Role'), 'user_roles', 'user_id')->withTimestamps();
    }
}
