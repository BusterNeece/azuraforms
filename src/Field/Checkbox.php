<?php
namespace AzuraForms\Field;

class Checkbox extends MultipleOptions
{
    public function getField(string $form_name): ?string
    {
        $field = '';
        foreach ($this->options['choices'] as $key => $val) {
            [$choice_val, $choice_attributes] = $this->getAttributeString($val);
            $field .= sprintf('<input type="checkbox" name="%1$s[]" id="%6$s_%3$s" value="%2$s" %4$s />' .
                '<label for="%6$s_%3$s">%5$s</label>',
                $this->getFullName(),
                $key,
                $this->slugify($this->getFullName()) . '_' . $this->slugify($key),
                (is_array($this->value) && in_array((string) $key, $this->value) ? 'checked="checked"' : '') . $choice_attributes,
                $choice_val,
                $form_name
            );
        }

        return $field;
    }
}
