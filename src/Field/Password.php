<?php
namespace AzuraForms\Field;

class Password extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'password';

        $this->options['min_length'] = $this->attributes['min_length'] ?? 0;
        unset($this->attributes['min_length']);

        $this->options['confirm'] = $this->attributes['confirm'] ?? null;
        unset($this->attributes('confirm'));

        $this->validators[] = function($value, $element) {
            if ($this->options['min_length'] && strlen($value) < $this->options['min_length']) {
                return sprintf('Must be more than %s characters.', $this->options['min_length']);
            }

            if (!empty($this->options['confirm'])) {
                $confirm_value = $element->getForm()->getField($this->options['confirm'])->getValue();

                if ($value !== $confirm_value) {
                    return 'Field and confirmation field do not match.';
                }
            }

            return true;
        };
    }
}
