<?php

namespace Nibble\NibbleForms\Field;

use Nibble\NibbleForms\Useful;
use Nibble\NibbleForms\Field;

class Text extends Field
{
    protected $label;
    protected $required = true;

    public $field_type = 'text';

    public function __construct($label, $attributes = array())
    {
        $this->label = $label;

        if (isset($attributes['required'])) {
            $this->required = $attributes['required'];

            if ($attributes['required']) {
                $attributes['required'] = 'required';
            }
        }

        if (isset($attributes['type'])) {
            $this->field_type = $attributes['type'];
            unset($attributes['type']);
        }

        $this->attributes = $attributes;
    }

    public function attributeString()
    {
        $class = '';

        if (!empty($this->error)) {
            $class = 'error';
        }

        $attribute_string = '';
        foreach ($this->attributes as $attribute => $val) {
            if ($attribute == 'class') {
                $class .= ' ' . $val;
            } else {
                $attribute_string .= $val ? ' ' . ($val === true ? $attribute : "$attribute=\"$val\"") : '';
            }
        }

        return [$attribute_string, $class];
    }

    public function returnField($form_name, $name, $value = '')
    {
        list($attribute_string, $class) = $this->attributeString();

        return array(
            'messages' => !empty($this->custom_error) && !empty($this->error) ? $this->custom_error : $this->error,
            'label' => $this->label === false
                ? false
                : sprintf('<label for="%s_%s">%s</label>', $form_name, $name, $this->label),
            'field' => sprintf('<input type="%1$s" name="%2$s" id="%6$s_%2$s" value="%3$s" %4$s class="%5$s" />',
                $this->field_type, $name, $this->escape($value), $attribute_string, $class, $form_name),
            'html' => $this->html
        );
    }

    public function validate($val)
    {
        if ($this->required) {
            if (Useful::stripper($val) === false) {
                $this->error[] = 'This field is required.';
            }
        }

        return !empty($this->error) ? false : true;
    }
}