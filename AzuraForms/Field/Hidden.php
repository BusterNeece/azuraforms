<?php
namespace AzuraForms\Field;

class Hidden extends Text
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        $this->attributes['type'] = 'hidden';
        $this->options['label'] = false;
    }

    public function renderView($show_empty = false): string
    {
        return null;
    }
}
