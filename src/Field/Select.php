<?php
namespace AzuraForms\Field;

final class Select extends Options
{
    public function getField($form_name): ?string
    {
        return sprintf(
            '<select name="%1$s" id="%2$s_%1$s">%3$s</select>',
            $this->getFullName(),
            $form_name,
            $this->_buildOptions($this->options['choices'], $this->value),
            $class,
            $attribute_string
        );
    }
}
