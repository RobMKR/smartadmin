<?php
namespace MgpLabs\SmartAdmin;

use Illuminate\Translation\TranslationServiceProvider as ServiceProvider;
use MgpLabs\SmartAdmin\Services\TranslationService;
use MgpLabs\SmartAdmin\Modules\Translation\Translator;

class SmartAdminTranslationServiceProvider extends ServiceProvider{

	/**
     * Override registerLoader Method to call custom translation Loader
     *
     * @return void
     */
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new TranslationService($app['files'], $app['path.lang']);
        });
    }

    public function register()
    {
        $this->registerLoader();

        $this->app->singleton('translator', function ($app) {

            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];

            $trans = new Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }

}