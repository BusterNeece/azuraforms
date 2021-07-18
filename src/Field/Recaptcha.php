<?php
namespace AzuraForms\Field;

class Recaptcha extends AbstractField
{
    public function configure(array $config = []): void
    {
        parent::configure($config);

        $this->validators[] = function($val) {
            $params = [
                'secret'    => $this->attributes['private_key'],
                'response'  => $val,
                'remoteip'  => $_SERVER['REMOTE_ADDR']
            ];

            $url = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query($params);
            $jsonRaw = file_get_contents($url);

            if (!empty($jsonRaw)) {
                $response = json_decode($jsonRaw, true);
                if ($response['success'] ?? false) {
                    return true;
                }
            }

            return 'Could not validate captcha.';
        };
    }

    public function getField(string $form_name): ?string
    {
        $field = <<<FIELD
<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
<div class="g-recaptcha" data-sitekey="%s" data-theme="dark"></div>
FIELD;

        return sprintf($field, $this->attributes['public_key']);
    }

    public function renderView($show_empty = false): string
    {
        return '';
    }
}
