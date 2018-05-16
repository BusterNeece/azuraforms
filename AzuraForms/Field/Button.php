<?php
namespace AzuraForms\Field;

class Button extends Text
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        $this->setValue($this->options['label']);
        unset($this->options['label']);

        $this->attributes['type'] = 'button';
    }

    public function getValue()
    {
        // Indicate that this field shouldn't be included in bulk value returns.
        return null;
    }

    public function isValid($new_value = null): bool
    {
        return true;
    }
}
