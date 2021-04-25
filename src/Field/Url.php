<?php
namespace AzuraForms\Field;

use const FILTER_VALIDATE_URL;

class Url extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'url';

        $this->validators[] = static function($value) {
            if (false === filter_var($value, FILTER_VALIDATE_URL)) {
                return 'Must be a valid URL.';
            }
            return true;
        };
    }
}
