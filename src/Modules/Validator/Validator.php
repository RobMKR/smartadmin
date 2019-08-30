<?php
/**
 * Override Laravel Validator
 * Return Error message Attribute
 * As Custom message
 * Depended on Project requirements
 */

namespace MgpLabs\SmartAdmin\Modules\Validator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as LaravelValidator;

class Validator extends LaravelValidator {

    /**
     * Get the displayable name of the attribute.
     *
     * @param  string  $attribute
     * @return string
     */
    protected function getAttribute($attribute)
    {
        $primaryAttribute = $this->getPrimaryAttribute($attribute);

        $expectedAttributes = $attribute != $primaryAttribute ? [$attribute, $primaryAttribute] : [$attribute];

        foreach ($expectedAttributes as $expectedAttributeName) {
            // The developer may dynamically specify the array of custom attributes
            // on this Validator instance. If the attribute exists in this array
            // it takes precedence over all other ways we can pull attributes.
            if (isset($this->customAttributes[$expectedAttributeName])) {
                return $this->customAttributes[$expectedAttributeName];
            }

            $line = Arr::get(
                $this->translator->trans('validation.attributes'),
                $expectedAttributeName
            );

            // We allow for the developer to specify language lines for each of the
            // attributes allowing for more displayable counterparts of each of
            // the attributes. This provides the ability for simple formats.
            if ($line) {
                return $line;
            }
        }

        // When no language line has been specified for the attribute and it is
        // also an implicit attribute we will display the raw attribute name
        // and not modify it with any replacements before we display this.
        if (isset($this->implicitAttributes[$primaryAttribute])) {
            return $attribute;
        }

        return 'L_' . Str::upper(Str::snake($attribute));
    }

    /**
     * Get the displayable name of the value.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return string
     */
    public function getDisplayableValue($attribute, $value)
    {
        if (isset($this->customValues[$attribute][$value])) {
            return $this->customValues[$attribute][$value];
        }

        $key = "validation.values.{$attribute}.{$value}";

        if (($line = $this->translator->trans($key)) !== $key) {
            return $line;
        }

        return 'L_' . Str::upper($value);
    }
}