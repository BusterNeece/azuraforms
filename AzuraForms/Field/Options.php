<?php
namespace AzuraForms\Field;

abstract class Options extends BaseOptions
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        $this->validators[] = function ($value) {
            if ($this->options['required'] || !empty($value)) {
                // Use array_keys to use the looser "in_array" check.
                $choice_keys = array_keys($this->options['choices']);
                if (!in_array($value, $choice_keys)) {
                    return 'Choice is not one of the available options.';
                }
            }

            return true;
        };
    }

    protected function _isEmpty($value): bool
    {
        return false;
    }
}
