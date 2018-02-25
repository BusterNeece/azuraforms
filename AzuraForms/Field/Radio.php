<?php
namespace AzuraForms\Field;

use AzuraForms\Useful;

class Radio extends Options
{
    protected function _getField($form_name, $name, $value = '')
    {
        $field = '';
        foreach ($this->options as $key => $val) {
            $attributes = $this->_getAttributeString($val);
            $field .= sprintf('<input type="radio" name="%1$s" id="%6$s_%3$s" value="%2$s" %4$s/>' .
                '<label for="%6$s_%3$s">%5$s</label>'
                , $name, $key, Useful::slugify($name) . '_' . Useful::slugify($key), ((string) $key === (string) $value ? 'checked="checked"' : '') . $attributes['string'], $attributes['val'], $form_name);
        }

        return $field;
    }
}
