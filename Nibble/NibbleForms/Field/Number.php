<?php

namespace Nibble\NibbleForms\Field;

use Nibble\NibbleForms\Useful;

class Number extends Text
{
    public function __construct($label, array $attributes = array())
    {
        parent::__construct($label, $attributes);

        $this->field_type = 'number';
    }

    public function validate($val)
    {
        if (!empty($this->error)) {
            return false;
        }

        if (parent::validate($val)) {
            if (Useful::stripper($val) !== false) {
                if (!filter_var($val, FILTER_VALIDATE_FLOAT)) {
                    $this->error[] = 'Must be numeric.';
                }
            }
        }

        return !empty($this->error) ? false : true;
    }
}
