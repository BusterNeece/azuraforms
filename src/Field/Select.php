<?php
namespace AzuraForms\Field;

final class Select extends Options
{
    public function getField($form_name): ?string
    {
        list($attribute_string, $class) = $this->_attributeString();

        return sprintf(
            '<select name="%1$s" id="%2$s_%1$s" class="%4%s" %5$s>%3$s</select>',
            $this->getFullName(),
            $form_name,
            $this->_buildOptions($this->options['choices'], $this->value),
            $class,
            $attribute_string
        );
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
            } else if ($val !== false) {
                $attribute_string .= ' '.($val === true ? $attribute : "$attribute=\"$val\"");
            }
        }

        return [$attribute_string, $class];
    }
}
