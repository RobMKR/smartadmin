<?php

Route::post('api/auth/login', 'MgpLabs\SmartAdmin\Controllers\AuthController@login');
Route::post('api/auth/register', 'MgpLabs\SmartAdmin\Controllers\AuthController@register');
Route::post('api/auth/logout', 'MgpLabs\SmartAdmin\Controllers\AuthController@logout');

Route::get('api/get-translations', 'MgpLabs\SmartAdmin\Controllers\CustomController@getAllTranslations');
Route::get('api/get-langs', 'MgpLabs\SmartAdmin\Controllers\CustomController@getLangs');

/*------------------------------------------------------------------------*/

/**
 * Middleware group for jwt auth only
 */
Route::group(['middleware' => ['jwt.auth']], function () {
    Route::post('api/refresh-token', 'MgpLabs\SmartAdmin\Controllers\AuthController@refreshToken');
    Route::get('api/generate-permissions', 'MgpLabs\SmartAdmin\Controllers\CustomController@generatePermissions');
    Route::post('api/auth/change-password', 'MgpLabs\SmartAdmin\Controllers\UsersController@changePassword');
    Route::get('api/get-all-default-model-fields', 'MgpLabs\SmartAdmin\Controllers\CustomController@getAllDefaultModelFields');
});

/** Middleware group for all authenticated request, except default models
 * jwt.auth => JWT token validity middleware
 * jwt.refresh => Refreshed JWT token for each request and append to response header as [{Authorization} : {Bearer:JWT_TOKEN}]
 * acl => Access Control Middleware, Checks user, that already passed jwt.auth middleware, has permission to resource or not|
 */
Route::group(['middleware' => ['jwt.auth', 'acl']], function (){

    Route::get('api/getDefaultModels', 'MgpLabs\SmartAdmin\Controllers\CustomController@getDefaultModels');
    Route::post('api/no-translation', 'MgpLabs\SmartAdmin\Controllers\CustomController@noTranslation');
    Route::post('api/save-configs', 'MgpLabs\SmartAdmin\Controllers\CustomController@saveUserConfigs');

});

/**
 * Middleware group for default models
 *
 * model_exists => Checking Default Model Existence
 */
Route::group(['middleware' => ['jwt.auth', 'model_exists', 'acl']], function () {
    Route::get('api/default/{model_name}', 'MgpLabs\SmartAdmin\Controllers\Controller@get');
    Route::post('api/default/{model_name}', 'MgpLabs\SmartAdmin\Controllers\Controller@post');

    // Route For delete one entity
    Route::delete('api/default/{model_name}/{id}', 'MgpLabs\SmartAdmin\Controllers\Controller@delete');

    // Route For Delete multiple entities
    Route::delete('api/default/{model_name}', 'MgpLabs\SmartAdmin\Controllers\Controller@delete');

    Route::get('api/report/{model_name}', 'MgpLabs\SmartAdmin\Controllers\ReportController@get');
});


/**
 * Default Model Group without ACL
 */
Route::group(['middleware' => ['jwt.auth', 'model_exists', ]], function (){
    Route::get('api/getModelFields/{model_name}', 'MgpLabs\SmartAdmin\Controllers\CustomController@getDefaultModelFields');
});