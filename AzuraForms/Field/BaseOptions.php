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
            $val = array_shift($attributes);

            foreach ($attributes as $attribute => $arg) {
                $attribute_string .= $arg ? ' ' . ($arg === true ? $attribute : "$attribute=\"$arg\"") : '';
            }
        }

        if ($this->options['escape_choices']) {
            $val = $this->escape($val);
        }

        return [$val, $attribute_string];
    }

    protected function _getFlattenedChoices(array $choices): array
    {
        $choices = [];
        foreach($choices as $choice_key => $choice_val) {
            if (is_array($choice_val)) {
                $choices = array_merge($choices, $this->_getFlattenedChoices($choice_val));
            } else {
                $choices[$choice_key] = $choice_val;
            }
        }

        return $choices;
    }

    protected function _buildOptions(array $choices, $selected = null): string
    {
        $field = '';

        foreach ($choices as $key => $val) {
            if (is_array($val)) {
                if ($this->options['escape_choices']) {
                    $key = $this->escape($key);
                }

                $field .= sprintf('<optgroup label="%s">%s</optgroup>',
                    $key,
                    $this->_buildOptions($val, $selected)
                );
            }

            list($choice_val, $choice_attributes) = $this->_getAttributeString($val);

            $is_selected = false;
            if ($selected) {
                $is_selected = (is_array($selected))
                    ? in_array($key, $selected)
                    : ((string)$key === (string)$selected);
            }

            $field .= sprintf('<option value="%s" %s>%s</option>',
                $key,
                ($is_selected ? 'selected="selected"' : '') . $choice_attributes,
                $choice_val
            );
        }

        return $field;
    }
}
