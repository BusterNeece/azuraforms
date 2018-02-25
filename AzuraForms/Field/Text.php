<?php
namespace AzuraForms\Field;

use AzuraForms\Useful;

class Text extends AbstractField
{
    public function __construct($label, $attributes = array())
    {
        $this->field_type = 'text';

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

    protected function _getField($form_name, $name, $value = '')
    {
        list($attribute_string, $class) = $this->_attributeString();

        return sprintf('<input type="%1$s" name="%2$s" id="%6$s_%2$s" value="%3$s" %4$s class="%5$s" />',
            $this->field_type, $name, $this->escape($value), $attribute_string, $class, $form_name);
    }

    protected function _attributeString()
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