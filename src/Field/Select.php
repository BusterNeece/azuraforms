<?php
namespace AzuraForms\Field;

class Select extends Options
{
    public function getField(string $form_name): ?string
    {
        [$attribute_string, $class] = $this->_attributeString();

        return sprintf(
            '<select name="%1$s" id="%2$s_%1$s" class="%4%s" %5$s>%3$s</select>',
            $this->getFullName(),
            $form_name,
            $this->buildOptions($this->options['choices'], $this->value),
            $class,
            $attribute_string
        );
    }

    protected function _attributeString(): array
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
