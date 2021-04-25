<?php
namespace AzuraForms\Field;

use AzuraForms\Form;

class Csrf extends Hidden
{
    public const SESSION_NAMESPACE = 'azuraforms_csrf';

    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->options['csrf_key'] = $this->attributes['csrf_key'] ?? Form::DEFAULT_FORM_NAME;
        unset($this->attributes['csrf_key']);

        $this->options['required'] = true;

        $this->attributes['autocomplete'] = 'off';

        $this->validators[] = function($form_token) {
            if ($this->verifyCsrf($form_token)) {
                return true;
            }

            return 'CSRF validation failure.';
        };
    }

    public function getField(string $form_name): ?string
    {
        $this->setValue($this->generateCsrf());

        return parent::getField($form_name);
    }

    protected function verifyCsrf(string $token): bool
    {
        return isset($_SESSION[self::SESSION_NAMESPACE][$this->options['csrf_key']])
            && hash_equals($_SESSION[self::SESSION_NAMESPACE][$this->options['csrf_key']], $token);
    }

    protected function generateCsrf(): string
    {
        if (!isset($_SESSION[self::SESSION_NAMESPACE])) {
            $_SESSION[self::SESSION_NAMESPACE] = [];
        }

        $new_token = bin2hex(random_bytes(32));

        $_SESSION[self::SESSION_NAMESPACE][$this->options['csrf_key']] = $new_token;

        return $new_token;
    }
}
