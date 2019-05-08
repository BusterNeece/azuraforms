<?php
namespace AzuraForms\Field;

class Date extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'date';
    }
}
