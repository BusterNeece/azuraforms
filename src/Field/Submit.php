<?php
namespace AzuraForms\Field;

final class Submit extends Button
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'submit';
    }
}