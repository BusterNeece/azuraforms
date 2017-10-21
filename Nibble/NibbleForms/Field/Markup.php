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
        $this->required = false;
    }

    protected function _getField($form_name, $name, $value = '')
    {
        return $this->markup;
    }

    public function validate($val)
    {
        return true;
    }
}