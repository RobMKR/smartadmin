<?php

namespace MgpLabs\SmartAdmin\Controllers;

use MgpLabs\SmartAdmin\Services\ACLService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JWTAuth;
use MgpLabs\SmartAdmin\Services\ApiResponseService;
use MgpLabs\SmartAdmin\Models\UserConfig;

class AuthController extends BaseController
{
    /**
     * Login Functional [Redirect with errors, if credentials not match]
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request){
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials, ['locale' => $request->get('locale', config('app.locale'))])) {
                return ApiResponseService::authError('CREDENTIALS_NOT_MATCH');
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return ApiResponseService::authError('COULD_NOT_CREATE_TOKEN');
        }

        $user = JWTAuth::authenticate($token);

        if(! $user->isActive()){
            return ApiResponseService::authError('L_USER_NOT_ACTIVE');
        }

        return ApiResponseService::successCustom([
            'data' => $user->toArray(),
            'token' => $token,
            'configs' => UserConfig::getConfigs($user->id),
            'permissions' => ACLService::getUserPermissions($user),
            'roles' => ACLService::getUserRoles($user),
        ]);
    }

    /**
     * Refresh JWT Token and send back
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request){
        $new_token = JWTAuth::setRequest($request)->parseToken()->refresh();
        return ApiResponseService::successCustom(['token' => $new_token, 'data' => []]);
    }
}