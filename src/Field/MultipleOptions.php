<?php
namespace AzuraForms\Field;

abstract class MultipleOptions extends BaseOptions
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->options['minimum_selected'] = $this->attributes['minimum_selected'] ?? 0;
        unset($this->attributes['minimum_selected']);

        $this->validators[] = function($value) {
            if (is_array($value)) {
                if ($this->options['minimum_selected'] && count($value) < $this->options['minimum_selected']) {
                    return sprintf('At least %d options must be selected', $this->options['minimum_selected']);
                }
            }
            return true;
        };
    }

    public function getSelectedValue()
    {
        $selected = [];
        $choices = $this->_getFlattenedChoices($this->options['choices']);

        if (is_array($this->value)) {
            foreach($this->value as $selected_key) {
                if (isset($choices[$selected_key])) {
                    $selected[] = $choices[$selected_key];
                }
            }
        }

        return $selected;
    }

    public function renderView($show_empty = false): string
    {
        $value = $this->getSelectedValue();

        if (empty($value) && !$show_empty) {
            return '';
        }

        if ($this->options['escape_choices']) {
            $value = array_map(function($choice) {
                return $this->escape($choice);
            }, $value);
        }

        $output = '';
        if (!empty($this->options['label'])) {
            $output .= '<dt>'.$this->options['label'].'</dt>';
        }
        $output .= '<dd>'.implode('<br>', $value).'</dd>';
        return $output;
    }
}
