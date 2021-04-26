<?php
namespace AzuraForms\Field;

use AzuraForms;

abstract class AbstractField implements FieldInterface
{
    /** @var AzuraForms\Form The parent form that contains this field, used for enhanced validation. */
    protected AzuraForms\Form $form;

    /** @var string The element name in the form. */
    protected string $name;

    /** @var string|null The group the element is associated with (if any). */
    protected ?string $group;

    /**
     * @var array The options associated with the field. These are internal configuration values
     * that are not printed out directly as arrays, potentially including:
     *  - label
     *  - required
     *  - choices (for multiple-choice items)
     */
    protected array $options = [];

    /** @var array Field attributes that are included in its HTML output. */
    protected array $attributes = [];

    /** @var array A list of errors associated with the field. */
    protected array $errors = [];

    /** @var mixed The current value of the input field. */
    protected $value;

    /** @var array Any input filters that are applied to this item. */
    protected array $filters = [];

    /** @var array Any validators that are applied to this item. */
    protected array $validators = [];

    /**
     * @param AzuraForms\Form $form
     * @param string $element_name
     * @param array $config
     * @param null $group
     */
    public function __construct(AzuraForms\Form $form, string $element_name, array $config = [], $group = null)
    {
        $this->form = $form;
        $this->name = $element_name;
        $this->group = $group;

        $this->configure($config);
    }

    public function configure(array $config = []): void
    {
        $this->options = [
            'required' => false,
        ];

        $option_names = ['label', 'label_class', 'required', 'choices', 'description', 'description_class', 'form_group_class', 'legend_class'];
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

    public function getForm(): AzuraForms\Form
    {
        return $this->form;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        return (null !== $this->group)
            ? $this->group.'_'.$this->name
            : $this->name;
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $new_value
     */
    public function setValue($new_value): void
    {
        $this->value = $this->filterValue($new_value);
    }

    public function clearValue(): void
    {
        $this->value = '';
    }

    /**
     * Clear all existing validators.
     */
    public function clearValidators(): void
    {
        $this->validators = [];
    }

    /**
     * @param callable $validator
     */
    public function addValidator(callable $validator): void
    {
        $this->validators[] = $validator;
    }

    /**
     * Clear all existing filters.
     */
    public function clearFilters(): void
    {
        $this->filters = [];
    }

    /**
     * @param callable $filter
     */
    public function addFilter(callable $filter): void
    {
        $this->filters[] = $filter;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setOption(string $key, $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Apply any input filters that are present on this element.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function filterValue($value)
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
     * @param mixed $body
     */
    public function addError($body): void
    {
        $this->errors[] = $body;
    }

    /**
     * Return an editable form control for this field.
     *
     * @param string $form_name
     *
     * @return string The rendered form element.
     */
    public function render(string $form_name): string
    {
        $output = '<div class="form-group '.($this->options['form_group_class'] ?? '').'" id="field_'.$this->name.'">';
        $output .= $this->getLabel($form_name);

        $output .= '<div class="form-field">';
        $output .= $this->getField($form_name);
        $output .= '</div>';

        if (!empty($this->options['description'])) {
            $output .= '<small class="help-block">'.$this->options['description'].'</small>';
        }

        if (!empty($this->errors)) {
            $output .= '<small class="help-block form-error">'.implode('<br>', $this->errors).'</small>';
        }

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
     * @param mixed|null $new_value
     * @return bool
     */
    public function isValid($new_value = null): bool
    {
        if (null !== $new_value) {
            $this->setValue($new_value);
        }

        return $this->validateValue($this->value);
    }

    /**
     * Internal handler to loop through validators (and handle required fields).
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateValue($value): bool
    {
        $this->errors = [];

        if ($this->options['required'] && $this->isEmpty($value)) {
            $this->errors[] = 'This field is required.';
            return false;
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
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value): bool
    {
        return empty($value);
    }

    /**
     * Return the field body HTML for this element.
     *
     * @param string $form_name
     *
     * @return null|string
     */
    abstract public function getField(string $form_name): ?string;

    /**
     * Return the label HTML for this element.
     *
     * @param string $form_name
     *
     * @return null|string
     */
    public function getLabel(string $form_name): ?string
    {
        if (empty($this->options['label'])) {
            return null;
        }

        $required = $this->options['required'] ? '<span class="text-danger" title="Required">*</span>' : '';
        return sprintf('<label for="%s_%s" class="%s">%s %s</label>',
            $form_name,
            $this->name,
            $this->options['label_class'] ?? '',
            $this->options['label'],
            $required
        );
    }

    /**
     * Escape a potentially user-supplied value prior to display.
     *
     * @param string|null $string
     *
     * @return string|null
     */
    protected function escape(?string $string): ?string
    {
        return (null === $string)
            ? null
            : htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
    }

    /**
     * Slugify a string using a specified replacement for empty characters
     *
     * @param string $text
     * @param string $replacement
     *
     * @return string
     */
    protected function slugify(string $text, $replacement = '-'): string
    {
        return strtolower(trim(preg_replace('/\W+/', $replacement, $text), '-'));
    }
}
