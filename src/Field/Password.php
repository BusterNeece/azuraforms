<?php
namespace AzuraForms\Field;

final class Password extends Text
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->attributes['type'] = 'password';

        $this->options['min_length'] = $this->attributes['min_length'] ?? 0;
        unset($this->attributes['min_length']);

        $this->validators[] = function($value) {
            if ($this->options['min_length'] && strlen($value) < $this->options['min_length']) {
                return sprintf('Must be more than %s characters.', $this->options['min_length']);
            }
            return true;
        };
    }
}
