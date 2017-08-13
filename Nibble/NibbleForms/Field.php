<?php
namespace Nibble\NibbleForms;

abstract class Field
{
    /** @var NibbleForm */
    protected $form;

    protected $attributes = [];

    public $custom_error = [];
    public $error = [];
    public $html = [
        'open_field' => false,
        'close_field' => false,
        'open_html' => false,
        'close_html' => false
    ];

    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * Return the current field, i.e label and input
     *
     * @param $form_name
     * @param $name
     * @param string $value
     * @return mixed
     */
    abstract public function returnField($form_name, $name, $value = '');

    /**
     * Validate the current field
     *
     * @param $val
     * @return mixed
     */
    abstract public function validate($val);

    /**
     * Apply custom error message from user to field
     * @param $message
     */
    public function errorMessage($message)
    {
        $this->custom_error[] = $message;
    }

    /**
     * Escape a potentially user-supplied value prior to display.
     *
     * @param $string
     * @return string
     */
    protected function escape($string)
    {
        static $flags;

        if (!isset($flags)) {
            $flags = ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0);
        }

        return htmlspecialchars($string, $flags, 'UTF-8');
    }
}
