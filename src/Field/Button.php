<?php
namespace AzuraForms\Field;

class Button extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->value = $this->options['label'];
        unset($this->options['label']);

        $this->attributes['type'] = 'button';
    }

    /**
     * @inheritdoc
     */
    public function setValue($new_value): void
    {
        // Don't allow the resetting of a submit value.
        return;
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

    public function renderView($show_empty = false): string
    {
        return null;
    }
}
