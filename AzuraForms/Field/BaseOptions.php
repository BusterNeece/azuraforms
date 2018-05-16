<?php
namespace AzuraForms\Field;

abstract class BaseOptions extends AbstractField
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        $this->options['escape_choices'] = $this->attributes['escape_choices'] ?? false;
        unset($this->attributes['escape_choices']);
    }

    abstract public function getSelectedValue();

    protected function _getAttributeString($val)
    {
        $attribute_string = '';
        if (is_array($val)) {
            $attributes = $val;
            $val = $val[0];
            unset($attributes[0]);
            foreach ($attributes as $attribute => $arg) {
                $attribute_string .= $arg ? ' ' . ($arg === true ? $attribute : "$attribute=\"$arg\"") : '';
            }
        }

        if ($this->options['escape_choices']) {
            $val = $this->escape($val);
        }

        return [$val, $attribute_string];
    }
}
