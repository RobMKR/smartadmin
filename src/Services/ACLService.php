<?php

namespace MgpLabs\SmartAdmin\Services;

use App\Models\RolePermission;

class ACLService extends BaseService
{

    /**
     * ACL models
     * Using "acl/c", "acl/r", "acl/u", "acl/d" for all of ACL models
     *
     * @var array
     */
    protected static $acl_models = [
        'permission',
        'role',
        'role_permission',
        'user_role'
    ];

    /**
     * Namespace for default models
     *
     * @var string
     */
    protected static $default_namespace = 'api/default/';

    /**
     * Get user permissions [id => resource]
     *
     * @param $user
     * @return array
     */
    public static function getUserPermissions($user){
        if($user->super){
            return ['/*'];
        }

        $permissions = [];

        foreach ($user->roles as $_role){
            foreach ($_role->permissions as $_permission) {
                $permissions[$_permission->id] = $_permission->resource;
            }
        }

        return $permissions;
    }

    /**
     * Get User Roles list [id => alias]
     *
     * @param $user
     * @return array
     */
    public static function getUserRoles($user){
        if($user->super){
            return ['super'];
        }

        $roles = [];

        foreach ($user->roles as $_role){
            $roles[$_role->id] = $_role->alias;
        }

        return $roles;
    }

    /**
     * Getting User Role ID's
     *
     * @param $user
     * @return array
     */
    public static function getUserRoleIds($user){
        $roles = $user->roles;
        $_roles = [];

        foreach($roles as $_role){
            $_roles[] = $_role->id;
        }

        return $_roles;
    }

    /**
     * Getting Role Permissions [List: id => resource]
     *
     * @param $user
     * @return array
     */
    public static function getRolesPermissions($user){
        $roles = self::getUserRoleIds($user);

        $permissions = RolePermission::whereIn('role_id', $roles)
            ->join('permissions', 'role_permissions.permission_id', '=' , 'permissions.id')
            ->get();

        $_permissions = self::toJsonArray($permissions);
        return $_permissions;
    }

    /**
     * Group Permission to one json array
     *
     * @param $permissions
     * @return mixed
     */
    public static function toJsonArray($permissions){
        $_permissions = [];
        foreach($permissions as $_permission){
            $exploded = explode('/', $_permission->resource);
            if($exploded[1] === '*'){
                $_permissions[$exploded[0]] = ['c', 'r', 'u', 'd'];
            }else{
                $_permissions[$exploded[0]][] = $exploded[1];
            }
        }
        return $_permissions;
    }

    /**
     * Get Resource name from uri
     *
     * @param $uri
     * @return string
     */
    public static function getResourceFromUri($uri){
        if(StringService::existInString(self::$default_namespace, $uri)){
            // Case when Model has DEFAULT type
            $resource = StringService::cutFromString(self::$default_namespace, $uri);
        }else{
            // Case when Model has CUSTOM type
            $resource = StringService::cutFromString('api/', $uri);
        }

        if(StringService::existInString(self::$acl_models, $resource)){
            // ACL Models
            $resource = 'acl/' .  explode('/', $resource)[1];
        }

        return $resource;
    }

    /**
     * Check Exist resource in permissions array or not
     *
     * @param $res
     * @param $permissions
     * @return bool
     */
    public static function exist($res, $permissions){
        $exploded = explode('/', $res);
        if(isset($permissions[$exploded[0]]) && in_array($exploded[1] , $permissions[$exploded[0]])){
            return true;
        }

        return false;
    }

    /**
     * Check User Permission to given resource
     *
     * @param $uri
     * @param $user
     * @return array
     */
    public static function checkAccess($uri, $user){
        // Give access to everything for Super Users
        if($user->super){
            return ['status' => true, 'param' => ''];
        }

        // Get All Permissions assigned to User
        $permissions = self::getRolesPermissions($user);

        // Get current Resource name
        $resource = self::getResourceFromUri($uri);

        if(self::exist($resource, $permissions)){
            return ['status' => true, 'param' => ''];
        }else{
            return ['status' => false, 'param' => 'L_ACCESS_DENIED ' . $resource];
        }
    }
}