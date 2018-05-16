<?php
namespace AzuraForms\Field;

class MultipleSelect extends MultipleOptions
{
    public function getField($form_name): ?string
    {
        $field = sprintf('<select name="%1$s[]" id="%2$s_%1$s" multiple="multiple">',
            $this->name,
            $form_name
        );

        foreach ($this->options['choices'] as $key => $val) {
            list($choice_val, $choice_attributes) = $this->_getAttributeString($val);
            $field .= sprintf('<option value="%s" %s>%s</option>',
                $key,
                (is_array($this->value) && in_array((string) $key, $this->value) ? 'selected="selected"' : '') . $choice_attributes,
                $choice_val
            );
        }
        $field .= '</select>';

        return $field;
    }
}