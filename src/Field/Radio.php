<?php
namespace AzuraForms\Field;

class Radio extends Options
{
    public function setValue($new_value)
    {
        if ($new_value === "") {
            $new_value = '0';
        }

        parent::setValue($new_value);
    }

    public function getField($form_name): ?string
    {
        $field = '';
        foreach ($this->options['choices'] as $key => $val) {
            list($choice_val, $choice_attributes) = $this->_getAttributeString($val);
            $field .= sprintf('<input type="radio" name="%1$s" id="%6$s_%3$s" value="%2$s" %4$s/>' .
                '<label for="%6$s_%3$s">%5$s</label>',
                $this->name,
                $key,
                $this->slugify($this->name) . '_' . $this->slugify($key),
                ((string)$key === (string)$this->value ? 'checked="checked"' : '') . $choice_attributes,
                $choice_val,
                $form_name
            );
        }

        return $field;
    }
}
