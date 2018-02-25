<?php
namespace AzuraForms\Field;

class Hidden extends Text
{
    public function __construct($label, array $attributes = array())
    {
        parent::__construct($label, $attributes);

        $this->field_type = 'hidden';
        $this->label = false;
    }
}
