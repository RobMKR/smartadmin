<?php

namespace MgpLabs\SmartAdmin\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Translation\FileLoader;
use MgpLabs\SmartAdmin\Models\Translation;
use MgpLabs\SmartAdmin\Models\Lang;

class TranslationService extends FileLoader
{
    /**
     * Translations Eloquent Collection
     *
     * @var
     */
    protected $translations;


    /**
     * Prefix Name of Database Translations
     *
     * @default 'db'
     * @var
     */
    public static $translation_db_prefix;

    /**
     * Translations Array
     *
     * @var array
     */
    public static $translations_array = [];

    /**
     * Translations that not found in DB
     * And needs to
     *
     * @var array
     */
    public static $not_founded_translations = [];

    /**
     * TranslationLoader constructor.
     *
     * @param Filesystem $files
     * @param string $path
     */
    public function __construct(Filesystem $files, $path)
    {
        static::$translation_db_prefix = config('smartadmin.translation_db_prefix', 'db');

        parent::__construct($files, $path);
    }

    /**
     * Create key:value pairs from Translation Collection
     * [ 'term' => 'text']
     *
     * @return array
     */
    protected function createTranslationsArray(){
        if(empty(static::$translations_array)){
            foreach ($this->translations as &$_translation) {
                static::$translations_array[$_translation['term']] = $_translation['text'];
            }
        }

        return static::$translations_array;
    }

    /**
     * Load Translations from Database by given locale
     *
     * @param $locale
     * @return array
     */
    protected function loadTranslations($locale){
        $this->translations = Translation::with(['translations' => function($query) use ($locale) {
            $query->where('locale', $locale);
        }])->get();

        return $this->createTranslationsArray();
    }

    /**
     * Load the messages for the given locale.
     *
     * @param string $locale
     * @param string $group
     * @param string $namespace
     *
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        if ( static::$translation_db_prefix === strtolower($group)) {

            if(! config('app.debug')){
                return Cache::remember(
                    "translations_{$locale}",
                    config('smartadmin.translation_cache_time', 60),
                    function () use ($locale) {
                        return $this->loadTranslations($locale);
                    }
                );
            }

            return $this->loadTranslations($locale);
        }

        return parent::load($locale, $group, $namespace);
    }

    /**
     * Resolve Given Key
     * If Not found in DB translations
     * Add To Not Found Translations array
     * After everything we need to save it
     *
     * @param $key
     */
    public static function resolveKey($key){
        $db_key = str_replace(static::$translation_db_prefix . '.', '', $key);

        if(! array_key_exists($db_key, static::$translations_array) && ! in_array($db_key, static::$not_founded_translations)){
            static::$not_founded_translations[] = $db_key;
        }
    }

    /**
     * Save All translations to database
     *
     * @return void
     */
    public static function saveNotFoundedTranslations(){
        if(static::$not_founded_translations){
            $langs = Lang::pluck('code', 'id');

            foreach(static::$not_founded_translations as $_term){
                $translation = (new Translation)->create([
                    'term' => $_term,
                ]);

                foreach($langs as $_locale){
                    $translation->{'text:' . $_locale} = $_term;
                }
                $translation->save();
            }
        }
    }
}