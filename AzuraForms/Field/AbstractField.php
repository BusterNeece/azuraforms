<?php
namespace AzuraForms\Field;

use AzuraForms;

abstract class AbstractField
{
    /** @var AzuraForms\ */
    protected $form;

    protected $label;
    protected $attributes = [];
    protected $required = false;

    public $field_type;
    public $custom_error = [];
    public $error = [];
    public $html = [
        'open_field' => false,
        'close_field' => false,
        'open_html' => false,
        'close_html' => false
    ];

    /**
     * @param NibbleForm $form
     */
    public function setForm(NibbleForm $form)
    {
        $this->form = $form;
    }

    /**
     * @return NibbleForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Return the current field, i.e label and input
     *
     * @param $form_name
     * @param $name
     * @param string $value
     * @return mixed
     */
    public function returnField($form_name, $name, $value = '')
    {
        return array(
            'messages' => !empty($this->custom_error) && !empty($this->error) ? $this->custom_error : $this->error,
            'label' => $this->_getLabel($form_name, $name, $value),
            'field' => $this->_getField($form_name, $name, $value),
            'html' => $this->html
        );
    }

    /**
     * Return the field body HTML for this element.
     *
     * @param $form_name
     * @param $name
     * @param string $value
     * @return string
     */
    abstract protected function _getField($form_name, $name, $value = '');

    /**
     * Return the label HTML for this element.
     *
     * @param $form_name
     * @param $name
     * @param string $value
     * @return bool|string
     */
    protected function _getLabel($form_name, $name, $value = '')
    {
        $label = false;
        if ($this->label !== false) {
            $required = $this->required ? '<span class="text-danger" title="Required">*</span>' : '';
            $label = sprintf('<label for="%s_%s">%s %s</label>', $form_name, $name, $this->label, $required);
        }

        return $label;
    }

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
