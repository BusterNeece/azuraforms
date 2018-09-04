<?php
namespace AzuraForms\Field;

class Number extends Text
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        $this->attributes['type'] = 'number';

        $this->validators[] = function($value) {
            if (false === filter_var($value, \FILTER_VALIDATE_FLOAT)) {
                return 'Must be numeric.';
            }
            return true;
        };
    }
}
