<?php
namespace AzuraForms\Field;

use AzuraForms;

abstract class AbstractField
{
    /** @var AzuraForms\Form The parent form that contains this field, used for enhanced validation. */
    protected $form;

    /** @var string The element name in the form. */
    protected $name;

    /**
     * @var array The options associated with the field. These are internal configuration values
     * that are not printed out directly as arrays, potentially including:
     *  - label
     *  - required
     *  - choices (for multiple-choice items)
     */
    protected $options = [];

    /** @var array Field attributes that are included in its HTML output. */
    protected $attributes = [];

    /** @var array A list of errors associated with the field. */
    protected $errors = [];

    /** @var mixed The current value of the input field. */
    protected $value;

    /** @var array Any input filters that are applied to this item. */
    protected $filters = [];

    /** @var array Any validators that are applied to this item. */
    protected $validators = [];

    /**
     * AbstractField constructor.
     *
     * @param AzuraForms\Form $form
     * @param $element_name
     * @param array $config
     */
    public function __construct(\AzuraForms\Form $form, $element_name, array $config = [])
    {
        $this->form = $form;
        $this->name = $element_name;
        $this->configure($config);
    }

    /**
     * Configure the field using the specified flat configuration.
     * @param array $config
     */
    public function configure(array $config = [])
    {
        $this->options = [
            'required' => false,
        ];

        $option_names = ['label', 'required', 'choices', 'description'];
        foreach($option_names as $option_name) {
            if (isset($config[$option_name])) {
                $this->options[$option_name] = $config[$option_name];
                unset($config[$option_name]);
            }
        }

        if (isset($config['options'])) {
            $this->options['choices'] = $config['options'];
            unset($config['options']);
        }

        if (isset($config['default'])) {
            $this->setValue($config['default']);
            unset($config['default']);
        }

        if (isset($config['value'])) {
            $this->setValue($config['value']);
            unset($config['value']);
        }

        if (isset($config['filters'])) {
            $this->filters = (array)$config['filters'];
            unset($config['filters']);
        }

        if (isset($config['filter'])) {
            $this->filters[] = $config['filter'];
            unset($config['filter']);
        }

        if (isset($config['validators'])) {
            $this->validators = (array)$config['validators'];
            unset($config['validators']);
        }

        if (isset($config['validator'])) {
            $this->validators[] = $config['validator'];
            unset($config['validator']);
        }

        $this->attributes = $config;
    }

    /**
     * @return AzuraForms\Form
     */
    public function getForm(): AzuraForms\Form
    {
        return $this->form;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $new_value mixed
     */
    public function setValue($new_value)
    {
        $this->value = $this->_filterValue($new_value);
    }

    /**
     * Apply any input filters that are present on this element.
     *
     * @param $value mixed
     * @return mixed
     */
    protected function _filterValue($value)
    {
        if (!empty($this->filters)) {
            foreach($this->filters as $filter) {
                /** @var callable $filter */
                $value = $filter($value, $this);
            }
        }

        return $value;
    }

    /**
     * Return an array of all existing error messages (if any exist).
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns whether this element has any errors logged for it.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Append a new error to the error log.
     *
     * @param $body
     */
    public function addError($body)
    {
        $this->errors[] = $body;
    }

    /**
     * Return an editable form control for this field.
     *
     * @param $form_name
     * @return string The rendered form element.
     */
    public function render($form_name): string
    {
        $output = '<div class="form-group" id="field_'.$this->name.'">';
        $output .= $this->getLabel($form_name);
        
        if (!empty($this->options['description'])) {
            $output .= '<small class="help-block">'.$this->options['description'].'</small>';
        }
        
        if (!empty($this->errors)) {
            $output .= '<small class="help-block form-error">'.implode('<br>', $this->errors).'</small>';
        }
        
        $output .= '<div class="form-field">';
        $output .= $this->getField($form_name);
        $output .= '</div>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Return a view-only list version of the form element and its value.
     *
     * @param bool $show_empty
     * @return string
     */
    public function renderView($show_empty = false): string
    {
        if (empty($this->value) && !$show_empty) {
            return '';
        }

        $output = '';
        if (!empty($this->options['label'])) {
            $output .= '<dt>'.$this->options['label'].'</dt>';
        }
        $output .= '<dd>'.$this->escape($this->value).'</dd>';
        return $output;
    }

    /**
     * Check the currently set value for validity.
     *
     * @return bool
     */
    public function isValid($new_value = null): bool
    {
        if ($new_value !== null) {
            $this->setValue($new_value);
        }

        return $this->_validateValue($this->value);
    }

    /**
     * Internal handler to loop through validators (and handle required fields).
     *
     * @param $value
     * @return bool
     */
    protected function _validateValue($value): bool
    {
        $this->errors = [];

        if ($this->options['required']) {
            if ($this->_isEmpty($value)) {
                $this->errors[] = 'This field is required.';
                return false;
            }
        }

        if (empty($this->validators)) {
            return true;
        }

        foreach($this->validators as $validator) {
            $validator_result = $validator($value, $this);

            if ($validator_result !== true) {
                $message = (is_string($validator_result)) ? $validator_result : 'Invalid value specified.';
                $this->errors[] = $message;
                return false;
            }
        }

        return true;
    }

    /**
     * Allow individual elements to change what the "is empty" criteria are for required fields.
     *
     * @param $value
     * @return bool
     */
    protected function _isEmpty($value): bool
    {
        return (empty($value));
    }

    /**
     * Return the field body HTML for this element.
     *
     * @param $form_name string
     * @return null|string
     */
    abstract public function getField($form_name): ?string;

    /**
     * Return the label HTML for this element.
     *
     * @param $form_name string
     * @return null|string
     */
    public function getLabel($form_name): ?string
    {
        if (empty($this->options['label'])) {
            return null;
        }

        $required = $this->options['required'] ? '<span class="text-danger" title="Required">*</span>' : '';
        return sprintf('<label for="%s_%s">%s %s</label>',
            $form_name,
            $this->name,
            $this->options['label'],
            $required
        );
    }

    /**
     * Escape a potentially user-supplied value prior to display.
     *
     * @param $string
     * @return string
     */
    protected function escape($string): string
    {
        static $flags;

        if (!isset($flags)) {
            $flags = ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0);
        }

        return htmlspecialchars($string, $flags, 'UTF-8');
    }

    /**
     * Slugify a string using a specified replacement for empty characters
     *
     * @param string $text
     * @param string $replacement
     * @return string
     */
    protected function slugify($text, $replacement = '-'): string
    {
        return strtolower(trim(preg_replace('/\W+/', $replacement, $text), '-'));
    }
}
