<?php
namespace AzuraForms\Field;

class Time extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'time';
    }
}
