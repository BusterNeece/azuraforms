<?php
namespace AzuraForms\Field;

use const FILTER_VALIDATE_EMAIL;

class Email extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'email';

        $this->validators[] = static function($value) {
            if (empty($value)) {
                return true;
            }
            if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return 'Must be a valid e-mail address';
            }
            return true;
        };

    }
}
