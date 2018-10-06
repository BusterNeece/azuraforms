<?php
namespace AzuraForms\Field;

class Recaptcha extends AbstractField
{
    public function configure(array $config = [])
    {
        parent::configure($config);

        $this->validators[] = function($val) {
            $params = [
                'secret'    => $this->attributes['private_key'],
                'response'  => $val,
                'remoteip'  => $_SERVER['REMOTE_ADDR']
            ];

            $url = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query($params);
            $response = json_decode(file_get_contents($url));

            if ($response->success) {
                return true;
            }

            return 'Could not validate captcha.';
        };
    }

    public function getField($form_name): ?string
    {
        $field = <<<FIELD
<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
<div class="g-recaptcha" data-sitekey="%S" data-theme="dark"></div>
FIELD;

        return sprintf($field, $this->attributes['public_key']);
    }

    public function renderView($show_empty = false): string
    {
        return null;
    }
}
