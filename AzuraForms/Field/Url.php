<?php
namespace AzuraForms\Field;

use AzuraForms\Useful;

class Url extends Text
{
    public function __construct($label, array $attributes = array())
    {
        parent::__construct($label, $attributes);

        $this->field_type = 'url';
    }

    public function validate($val)
    {
        if (!empty($this->error)) {
            return false;
        }
        if (parent::validate($val)) {
            if (Useful::stripper($val) !== false) {
                if (!filter_var($val, FILTER_VALIDATE_URL)) {
                    $this->error[] = 'must be a valid URL';
                }
            }
        }

        return !empty($this->error) ? false : true;
    }
}
