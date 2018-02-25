<?php
namespace AzuraForms\Field;

class Time extends Text
{
    public function __construct($label, array $attributes = array())
    {
        parent::__construct($label, $attributes);

        $this->field_type = 'time';
    }
}
