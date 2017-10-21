<?php

namespace Nibble\NibbleForms\Field;

use Nibble\NibbleForms\Useful;

class Password extends Text
{
    protected $confirm = false;
    protected $min_length = false;
    protected $alphanumeric = false;

    public function __construct($label, $attributes = array())
    {
        if (isset($attributes['alphanumeric'])) {
            $this->alphanumeric = $attributes['alphanumeric'];
            unset($attributes['alphanumeric']);
        }
        if (isset($attributes['min_length'])) {
            $this->min_length = $attributes['min_length'];
            unset($attributes['min_length']);
        }
        if (isset($attributes['confirm'])) {
            $this->confirm = $attributes['confirm'];
            unset($attributes['confirm']);
        }

        parent::__construct($label, $attributes);

        $this->field_type = 'password';
    }

    public function validate($val)
    {
        if (!empty($this->error)) {
            return false;
        }

        if (parent::validate($val)) {
            if (Useful::stripper($val) !== false) {
                if ($this->min_length && strlen($val) < $this->min_length) {
                    $this->error[] = sprintf('Must be more than %s characters.', $this->min_length);
                }
                if ($this->alphanumeric && (!preg_match("/[A-Za-z]+/", $val) || !preg_match("/[0-9]+/", $val))) {
                    $this->error[] = 'Must have at least one alphabetic character and one numeric character.';
                }
            }
        }

        if ($this->confirm) {
            $other_val = $this->form->getData($this->confirm, true);
            if (strcmp($val, $other_val) !== 0) {
                $this->error[] = 'The passwords provided do not match.';
            }
        }

        return !empty($this->error) ? false : true;
    }

    public function addConfirmation($field_name, $attributes = array())
    {
        $this->form->addField($field_name, 'password', $attributes + $this->attributes);
        $this->confirm = $field_name;
    }

}
