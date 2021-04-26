<?php
namespace AzuraForms\Field;

class Button extends Text
{
    use Traits\NullValueTrait;

    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->value = $this->options['label'];
        unset($this->options['label']);

        $this->attributes['type'] = 'button';
    }

    public function isValid($new_value = null): bool
    {
        return true;
    }

    public function renderView($show_empty = false): string
    {
        return '';
    }
}
