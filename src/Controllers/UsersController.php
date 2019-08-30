<?php

namespace MgpLabs\SmartAdmin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use MgpLabs\SmartAdmin\Models\User;
use JWTAuth;
use MgpLabs\SmartAdmin\Services\ApiResponseService;

class UsersController extends BaseController
{
    /**
     * User Instance
     *
     * @var User
     */
    protected $User;

    /**
     * ChangePassword rules
     *
     * @var array
     */
    protected $change_password_rules = [
        'old_password' => 'required',
        'password' => 'required|min:6|confirmed|different:old_password'
    ];

    /**
     * Give model dependencies
     *
     * @param User $user
     */
    public function __construct(User $user){
        $this->User = User::getAuthenticatedUser();
        parent::__construct();
    }

    /**
     * Change User Password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request){
        $Validator = Validator::make($request->all(), $this->change_password_rules);

        if($Validator->fails()){
            return ApiResponseService::error('VALIDATION_ERROR', 422, $Validator->errors());
        }

        if(Hash::check($request->get('old_password'), $this->User->getAuthPassword())){
            $this->User->password = Hash::make($request->get('password'));
            $this->User->save();
            return ApiResponseService::success([], 'PASSWORD_CHANGED');
        }

        return ApiResponseService::error("OLD_PASSWORD_NOT_MATCH", 422);
    }

}
