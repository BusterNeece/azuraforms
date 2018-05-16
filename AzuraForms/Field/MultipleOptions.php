<?php
namespace AzuraForms\Field;

abstract class MultipleOptions extends BaseOptions
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        $this->options['minimum_selected'] = $this->attributes['minimum_selected'] ?? 0;
        unset($this->attributes['minimum_selected']);

        $this->validators[] = function($value) {
            if (is_array($value)) {
                if ($this->options['minimum_selected'] && count($value) < $this->options['minimum_selected']) {
                    return sprintf('At least %d options must be selected', $this->options['minimum_selected']);
                }
            }
            return true;
        };
    }
}
