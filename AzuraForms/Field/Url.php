<?php
namespace AzuraForms\Field;

class Url extends Text
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        $this->attributes['type'] = 'url';

        $this->validators[] = function($value) {
            if (!filter_var($value, \FILTER_VALIDATE_URL)) {
                return 'Must be a valid URL.';
            }
            return true;
        };
    }
}
