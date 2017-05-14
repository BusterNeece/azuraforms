<?php
namespace Nibble\NibbleForms\Field;

use Nibble\NibbleForms\Field;

/**
 * Class Markup
 * @package Nibble\NibbleForms\Field
 *
 * A generic "markup" field for including inline HTML in a form.
 */
class Markup extends Field
{
    public $error = [];

    protected $label;

    protected $markup;

    public function __construct($label = 'CAPTCHA', $attributes = [])
    {
        $this->label = $label;
        $this->markup = $attributes['markup'];
    }

    public function returnField($form_name, $name, $value = '')
    {
        return [
            'messages' => !empty($this->custom_error) && !empty($this->error) ? $this->custom_error : $this->error,
            'label' => $this->label == false ? false : sprintf('<label for="%s">%s</label>', $name, $this->label),
            'field' => $this->markup,
            'html' => $this->html
        ];
    }

    public function validate($val)
    {
        return true;
    }
}