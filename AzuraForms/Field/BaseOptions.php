<?php
namespace AzuraForms\Field;

abstract class BaseOptions extends AbstractField
{
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

        return [$val, $attribute_string];
    }
}
