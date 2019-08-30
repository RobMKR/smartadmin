<?php

return [

	'basic_auth' => false,

    'url' => '',

    /*
    |--------------------------------------------------------------------------
    | Default Model Names
    |--------------------------------------------------------------------------
    |
    | Here you may specify all default models, that worked on smart admin
    |
    */

    'defaultmodels' => [
        
    ],

    /*
    |--------------------------------------------------------------------------
    | Vendor Model Names
    |--------------------------------------------------------------------------
    |
    | Here you may specify all default models  form vendor, that worked on smart admin
    |
    */

    'vendormodels' => [
        'lang' => [
            'value' => 'lang',
            'name' => 'L_LANG',
            'category' => 'L_SETTING_CAT',
            'display' => true
        ],
        'translation' => [
            'value' => 'translation',
            'name' => 'L_TRANSLATION',
            'category' => 'L_SETTING_CAT',
            'display' => true
        ],
        'permission' => [
            'value' => 'permission',
            'name' => 'L_PERMISSION',
            'category' => 'L_SETTING_CAT',
            'display' => true
        ],
        'user' => [
            'value' => 'user',
            'name' => 'L_USER',
            'category' => 'L_SETTING_CAT',
            'display' => true
        ],
        'role' => [
            'value' => 'role',
            'name' => 'L_ROLE',
            'category' => 'L_SETTING_CAT',
            'display' => true
        ],
        'role_permission' => [
            'value' => 'role_permission',
            'name' => 'L_ROLE_PERMISSION',
            'category' => 'L_SETTING_CAT',
            'display' => false
        ],
        'user_role' => [
            'value' => 'user_role',
            'name' => 'L_USER_ROLE',
            'category' => 'L_SETTING_CAT',
            'display' => false
        ],
        'export' => [
            'value' => 'export',
            'name' => 'L_EXPORT_SETTINGS',
            'category' => 'L_SETTING_CAT',
            'display' => true
        ],
        'export_field' => [
            'value' => 'export_field',
            'name' => 'L_EXPORT_FIELDS',
            'category' => 'L_SETTING_CAT',
            'display' => true
        ],
        'report' => [
            'value' => 'report',
            'name' => 'L_REPORT',
            'category' => 'L_REPORTS_CAT',
            'display' => true
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cross Origin Request Parameters
    |--------------------------------------------------------------------------
    */

    'cors' => [
        'allow-methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'allow-headers' => 'Origin, Content-Type, X-Auth-Token, Authorization, X-Requested-With',
        'allow-origin' => '*'
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Log Parameters
    |--------------------------------------------------------------------------
    */

    'logs' => [
        'request' => [
            'except' => [
                '/api/auth/login',
                '/api/auth/logout',
                '/api/default/translation'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination limits by model type
    |--------------------------------------------------------------------------
    |
    | Here you may specify additional classes to include in the compiled file
    | generated by the `artisan optimize` command. These should be classes
    | that are included on basically every request into the application.
    |
    */

    'pagelimits' => [
        'user' => 20,
        'unique' => 50,
        'default' =>15
    ],
	
	/*
    |--------------------------------------------------------------------------
    | Identify locales are upper case or lower case
    |--------------------------------------------------------------------------
    |
    | If set to true, smartadmin must use locales in lowercase, if set to false, or not set,
    | It will use uppercase (for old versions only)
    |
    */
	
    'locale_is_lower' => true,
	
	/*
    |--------------------------------------------------------------------------
    | Identify translation prefix
    |--------------------------------------------------------------------------
    |
    | Used in TranslationService to load translations from db
    | Only for this prefix
    |
    */
	
	'translation_db_prefix' => 'db',
	
	/*
    |--------------------------------------------------------------------------
    | Identify translation cache ttl
    |--------------------------------------------------------------------------
    |
    | Cache will remember translation for given ttl
    | Set it in minutes. By default setted to 60mins = 1 hour
    |
    */
	
	'translation_cache_time' => 60,
	
	
];