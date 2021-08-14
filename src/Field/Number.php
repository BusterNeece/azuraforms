<?php
namespace AzuraForms\Field;

use const FILTER_VALIDATE_FLOAT;

class Number extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'number';

        $this->validators[] = static function($value) {
            if (false === filter_var($value, FILTER_VALIDATE_FLOAT)) {
                return 'Must be numeric.';
            }
            return true;
        };

        $this->filters[] = function($value) {
            if (strpos($value, '.') === false) {
                return (int)$value;
            }

            $step = (string)($this->attributes['step'] ?? 1);
            $decimals = (strpos($step, '.') === false)
                ? 0
                : strlen($step) - strrpos($step, '.') - 1;

            return round((float)$value, $decimals);
        };
    }
}
