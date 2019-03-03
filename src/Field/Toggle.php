<?php
namespace AzuraForms\Field;

final class Toggle extends BaseOptions
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->options['selected_text'] = $this->attributes['selected_text'] ?? 'Yes';
        $this->options['deselected_text'] = $this->attributes['deselected_text'] ?? 'No';
        unset($this->attributes['selected_text'], $this->attributes['deselected_text']);
    }

    public function setValue($new_value): void
    {
        parent::setValue((bool)$new_value);
    }

    public function getField($form_name): ?string
    {
        [$attribute_string, $class] = $this->_attributeString();

        if (true === (bool)$this->value) {
            $attribute_string .= ' checked="checked"';
        }

        return sprintf(
            '<input type="hidden" name="%1$s" value="0" />' .
            '<input type="checkbox" name="%1$s" id="%5$s_%1$s" value="1" %2$s class="%4$s" />' .
            '<label for="%5$s_%1$s">%3$s</label>',
            $this->getFullName(),
            $attribute_string,
            $this->options['label'],
            $class,
            $form_name
        );
    }

    protected function _attributeString()
    {
        $class = 'toggle-switch';

        if (!empty($this->error)) {
            $class .= ' error';
        }

        $attribute_string = '';
        foreach ($this->attributes as $attribute => $val) {
            if ('class' === $attribute) {
                $class .= ' ' . $val;
            } else if (false !== $val) {
                $attribute_string .= ' '.($val === true ? $attribute : "$attribute=\"$val\"");
            }
        }

        return [$attribute_string, $class];
    }

    public function getSelectedValue()
    {
        return $this->getValue();
    }

    public function renderView($show_empty = false): string
    {
        $value = $this->getSelectedValue();

        return ($value)
            ? $this->options['selected_text']
            : $this->options['deselected_text'];
    }
}
