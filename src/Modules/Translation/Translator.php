<?php

namespace MgpLabs\SmartAdmin\Modules\Translation;

use Illuminate\Translation\Translator as LaravelTranslator;
use MgpLabs\SmartAdmin\Services\TranslationService;


class Translator extends LaravelTranslator
{
    /**
     * Get the translation for a given key from the JSON translation files.
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string  $locale
     * @return string
     */
    public function getFromJson($key, array $replace = [], $locale = null)
    {

        $locale = $locale ?: $this->locale;

        // For JSON translations, there is only one file per locale, so we will simply load
        // that file and then we will be ready to check the array for the key. These are
        // only one level deep so we do not need to do any fancy searching through it.
        $this->load('*', '*', $locale);

        $line = isset($this->loaded['*']['*'][$locale][$key])
            ? $this->loaded['*']['*'][$locale][$key] : null;

        // If we can't find a translation for the JSON key, we will attempt to translate it
        // using the typical translation file. This way developers can always just use a
        // helper such as __ instead of having to pick between trans or __ with views.
        if (! isset($line)) {

            $fallback = $this->get($key, $replace, $locale);

            TranslationService::resolveKey($key);

            if ($fallback !== $key) {
                return $fallback;
            }
        }

        return $this->makeReplacements($line ?: $key, $replace);
    }
}