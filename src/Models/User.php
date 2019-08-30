<?php
namespace MgpLabs\SmartAdmin\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use MgpLabs\SmartAdmin\Services\ApiResponseService;
use JWTAuth;

class User extends SmartAdminBaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    protected $rels = [
        'roles' => [
            'model' => 'Role'
        ]
    ];

    protected $fillable = [
        'email',
        'password',
        'active'
    ];

    protected $hidden = [
        'password'
    ];

    protected $invisible = [
        'password'
    ];

    protected $multiselect = [
        'user_role' => [
            'model' => 'user_role',
            'as' => 'user_id',
            'fk' => 'role_id',
            'rel_name' => 'roles'
        ]
    ];

    protected $hashable = [
        'password'
    ];

    protected $rules = [
        'email' => 'required|unique:users,email',
        'password' => 'confirmed|min:6',
        'active' => 'required|in:active,inactive'
    ];

    /**
     * Roles Relation Many To Many
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany($this->modelName('Role'), 'user_roles', 'user_id')->withTimestamps();
    }

    /**
     * Get User from JWT Token
     *
     * @return User
     */
    public static function getAuthenticatedUser(){
        try {
            if (! $user = JWTAuth::parseToken()->toUser()) {
                ApiResponseService::forceError('USER_NOT_FOUND', 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            ApiResponseService::forceError('TOKEN_EXPIRED', $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            ApiResponseService::forceError('TOKEN_INVALID', $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            ApiResponseService::forceError('TOKEN_ABSENT', $e->getStatusCode());
        }
        // the token is valid and we have found the user via the sub claim
        return $user;
    }

    /**
     * Get user activity
     *
     * @return bool
     */
    public function isActive(){
        return $this->active === 'active' ? true : false;
    }

}
