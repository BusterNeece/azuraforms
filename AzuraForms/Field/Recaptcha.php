<?php
namespace AzuraForms\Field;

class Recaptcha extends AbstractField
{
    public $error = [];

    protected $label;

    protected $attributes;

    public function __construct($label = 'CAPTCHA', $attributes = [])
    {
        $this->label = $label;
        $this->attributes = $attributes;
    }

    protected function _getField($form_name, $name, $value = '')
    {
        $field = <<<FIELD
<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
<div class="g-recaptcha" data-sitekey="%S" data-theme="dark"></div>
FIELD;

        sprintf($field, $this->attributes['public_key']);
    }

    public function validate($val)
    {
        $params = [
            'secret' => $this->attributes['private_key'],
            'response' => $val,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $url = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query($params);
        $response = json_decode(file_get_contents($url));

        return $response->success;
    }

}
