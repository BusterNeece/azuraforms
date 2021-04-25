<?php
namespace AzuraForms\Field;

abstract class BaseOptions extends AbstractField
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->options['escape_choices'] = $this->attributes['escape_choices'] ?? false;
        unset($this->attributes['escape_choices']);
    }

    abstract public function getSelectedValue();

    protected function _getAttributeString($val): array
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
        $flattened = [];
        foreach($choices as $choice_key => $choice_val) {
            if (is_array($choice_val)) {
                $flattened = array_merge($flattened, $this->_getFlattenedChoices($choice_val));
            } else {
                $flattened[$choice_key] = $choice_val;
            }
        }

        return $flattened;
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
            } else {
                [$choice_val, $choice_attributes] = $this->_getAttributeString($val);

                $is_selected = false;
                if (null !== $selected) {
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
        }

        return $field;
    }
}
