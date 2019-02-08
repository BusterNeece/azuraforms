<?php
namespace AzuraForms\Field;

final class Time extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'time';
    }
}
