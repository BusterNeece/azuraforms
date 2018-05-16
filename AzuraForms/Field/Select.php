<?php
namespace AzuraForms\Field;

class Select extends Options
{
    public function getField($form_name): ?string
    {
        $field = sprintf('<select name="%1$s" id="%2$s_%1$s">', $this->name, $form_name);
        foreach ($this->options['choices'] as $key => $val) {
            list($choice_val, $choice_attributes) = $this->_getAttributeString($val);
            $field .= sprintf('<option value="%s" %s>%s</option>',
                $key,
                ((string) $key === (string) $this->value ? 'selected="selected"' : '') . $choice_attributes,
                $choice_val
            );
        }
        $field .= '</select>';

        return $field;
    }
}
