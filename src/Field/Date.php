<?php
namespace AzuraForms\Field;

final class Date extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'date';
    }
}
