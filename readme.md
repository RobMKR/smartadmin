For Laravel 5.4 change Model::create methods to Model::fill

**_********************Service Providers need to be ran :**_******************** 

        Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,
        Felixkiss\UniqueWithValidator\UniqueWithValidatorServiceProvider::class,
        Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider::class,
        Dimsav\Translatable\TranslatableServiceProvider::class,
        OwenIt\Auditing\AuditingServiceProvider::class,
        Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class,
        MgpLabs\SmartAdmin\SmartAdminServiceProvider::class,
		MgpLabs\SmartAdmin\SmartAdminTranslationServiceProvider::class,
		Maatwebsite\Excel\ExcelServiceProvider::class,
        
        
        
**_********************Aliases need to be loaded :**_******************** 

        'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class, app.php config
        'Excel' => Maatwebsite\Excel\Facades\Excel::class, http://www.maatwebsite.nl/laravel-excel/docs 

        \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
        
**_******************** Middlewares need to added to Kernel.php :**_********************         
        
        $routeMiddleware[] = [
            'jwt.auth' => \Tymon\JWTAuth\Middleware\GetUserFromToken::class,
            'jwt.refresh' => \Tymon\JWTAuth\Middleware\RefreshToken::class,
            'model_exists' => \MgpLabs\SmartAdmin\Middleware\DefModelExists::class,
            'acl' => \MgpLabs\SmartAdmin\Middleware\VerifyAccess::class,
        ];
        
        $middleware[] = [
            \MgpLabs\SmartAdmin\Middleware\Cors::class,
            \MgpLabs\SmartAdmin\Middleware\Logger::class,
            \MgpLabs\SmartAdmin\Middleware\LocalizeRequests::class,    
        ];
        
        
**_********************Changes before bootsrap :**_******************** 

    1.)     Change from 'jwt.php' config file User Model Path from 'App/User' 
            to 'MgpLabs/SmartAdmin/Models/User'
        
    2.)     Change form 'auth.php' config file user provider path from 
            'App/User' to 'MgpLabs/SmartAdmin/Models/User'     
            
**_********************Config Changes :**_********************            
            
        Add Disk to disks for default model files    
            
        'default' => [
            'driver' => 'local',
            'root'   => public_path() . '\default_images',
            'url_path' => 'default_images/'
        ],
