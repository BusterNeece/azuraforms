<?php
namespace AzuraForms\Field;

class Csrf extends Hidden
{
    public const SESSION_NAMESPACE = 'azuraforms_csrf';

    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->options['csrf_key'] = $this->attributes['csrf_key'] ?? \AzuraForms\Form::DEFAULT_FORM_NAME;
        unset($this->attributes['csrf_key']);

        $this->options['required'] = true;

        $this->attributes['autocomplete'] = 'off';

        $this->validators[] = function($form_token) {
            if (!$this->verifyCsrf($form_token)) {
                return 'CSRF validation failure.';
            }
        };
    }

    public function getField($form_name): ?string
    {
        $this->setValue($this->generateCsrf());

        return parent::getField($form_name);
    }

    protected function verifyCsrf(string $token): bool
    {
        if (isset($_SESSION[self::SESSION_NAMESPACE][$this->options['csrf_key']])) {
            if (hash_equals($_SESSION[self::SESSION_NAMESPACE][$this->options['csrf_key']], $form_token)) {
                return true;
            }
        }

        return false;
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