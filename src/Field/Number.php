<?php
namespace AzuraForms\Field;

class Number extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'number';

        $this->validators[] = function($value) {
            if (false === filter_var($value, \FILTER_VALIDATE_FLOAT)) {
                return 'Must be numeric.';
            }
            return true;
        };

        $this->filters[] = function($value) {
            $step = (string)($this->attributes['step'] ?? 1);

            $decimals = ((int)$step == $step)
                ? 0
                : strlen($step) - strrpos($step, '.') - 1;

            return round($value, $decimals);
        };
    }
}
