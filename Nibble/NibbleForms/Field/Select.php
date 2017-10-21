<?php
namespace Nibble\NibbleForms\Field;

class Select extends Options
{
    protected function _getField($form_name, $name, $value = '')
    {
        $field = sprintf('<select name="%1$s" id="%2$s_%1$s">', $name, $form_name);
        foreach ($this->options as $key => $val) {
            $attributes = $this->getAttributeString($val);
            $field .= sprintf('<option value="%s" %s>%s</option>', $key, ((string) $key === (string) $value ? 'selected="selected"' : '') . $attributes['string'], $attributes['val']);
        }
        $field .= '</select>';

        return $field;
    }
}
