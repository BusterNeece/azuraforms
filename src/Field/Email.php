<?php
namespace AzuraForms\Field;

final class Email extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'email';

        $this->validators[] = function($value) {
            if (false === filter_var($value, \FILTER_VALIDATE_EMAIL)) {
                return 'Must be a valid e-mail address';
            }
            return true;
        };

    }
}
