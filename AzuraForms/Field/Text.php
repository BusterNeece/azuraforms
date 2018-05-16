<?php
namespace AzuraForms\Field;

class Text extends AbstractField
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        if (!isset($this->attributes['type'])) {
            $this->attributes['type'] = 'text';
        }
    }

    public function getField($form_name): ?string
    {
        list($attribute_string, $class) = $this->_attributeString();

        return sprintf('<input type="%1$s" name="%2$s" id="%6$s_%2$s" value="%3$s" %4$s class="%5$s" />',
            $this->attributes['type'],
            $this->name,
            $this->escape($this->value),
            $attribute_string,
            $class,
            $form_name
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
            } else {
                $attribute_string .= $val ? ' ' . ($val === true ? $attribute : "$attribute=\"$val\"") : '';
            }
        }

        return [$attribute_string, $class];
    }
}