<?php
namespace AzuraForms;

abstract class AbstractForm implements FormInterface
{
    public const DEFAULT_FORM_NAME = 'azuraforms_form';

    /** @var array The form's configuration options. */
    protected $options;

    /** @var array An array of "belongsTo" lookup groups (for processing data). */
    protected $groups;

    /** @var Field\AbstractField[] */
    protected $fields;

    /** @var string The form name, used in the <form> tag and in general. */
    protected $name = self::DEFAULT_FORM_NAME;

    /**
     * @var array An array of field type names that convert to other field classes.
     */
    protected $field_name_conversions = [
        'checkboxes'    => 'Checkbox',
        'multicheckbox' => 'Checkbox',
        'multiselect'   => 'MultipleSelect',
        'textarea'      => 'TextArea',
    ];

    /** @var array A list of namespaces in which to look for fields */
    protected $field_namespaces = [
        '\\AzuraForms\\Field',
    ];

    /** @var array */
    protected $errors = [];

    /**
     * @param array $options
     * @throws Exception\FieldAlreadyExists
     * @throws Exception\FieldClassNotFound
     */
    public function __construct(array $options = [])
    {
        $this->configure($options);
    }

    /**
     * Get the cleaned-up flatfile configuration for this form.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Retrieve an already added field.
     *
     * @param string $key
     * @return Field\FieldInterface
     * @throws Exception\FieldNotFound
     */
    public function getField($key): Field\FieldInterface
    {
        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        }

        throw new Exception\FieldNotFound(sprintf('Field name "%s" not found.', $key));
    }

    /**
     * Check if a field exists
     *
     * @param string $field
     * @return boolean
     */
    public function hasField($field): bool
    {
        return isset($this->fields[$field]);
    }

    /**
     * Add a field to the form instance.
     *
     * @param string $field_name
     * @param string $type
     * @param array $attributes
     * @param null $group
     * @param bool $overwrite
     * @return string The finalized (and group-prefixed) element name for the element.
     * @throws Exception\FieldAlreadyExists
     * @throws Exception\FieldClassNotFound
     */
    public function addField($field_name, $type = 'text', array $attributes = [], $group = null, $overwrite = false): string
    {
        $class = $this->_getFieldClass($type);

        /** @var Field\AbstractField $field */
        $field = new $class($this, $field_name, $attributes, $group);
        $full_field_name = $field->getFullName();

        if (isset($this->fields[$full_field_name]) && !$overwrite) {
            throw new Exception\FieldAlreadyExists(sprintf('Field with name "%s" already exists.', $full_field_name));
        }

        $this->fields[$full_field_name] = $field;

        if (null !== $group) {
            $this->groups[$group][] = $full_field_name;
        }

        return $full_field_name;
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * Set the name of the form
     *
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * Get form name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $error_message
     */
    public function addError($error_message): void
    {
        $this->errors[] = $error_message;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return (count($this->errors) > 0);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool Whether the form (including its individual fields) have any errors.
     */
    public function hasAnyErrors(): bool
    {
        return (count($this->getAllErrors()) > 0);
    }

    /**
     * @return Error[] All errors associated with the form, including its individual fields.
     */
    public function getAllErrors(): array
    {
        static $all_errors;

        if (null === $all_errors) {
            $all_errors = [];

            foreach($this->errors as $error) {
                $all_errors[] = new Error($error, null);
            }

            foreach($this->fields as $field) {
                if ($field->hasErrors()) {
                    $field_options = $field->getOptions();
                    $field_label = $field_options['label'] ?? $field->getName();

                    foreach($field->getErrors() as $error) {
                        $all_errors[] = new Error($error, $field_label);
                    }
                }
            }
        }

        return $all_errors;
    }

    /**
     * Iterate through configuration options and set up each individual form element.
     *
     * @param array $options
     * @throws Exception\FieldAlreadyExists
     * @throws Exception\FieldClassNotFound
     */
    public function configure(array $options): void
    {
        if (isset($options['name'])) {
            $this->name = $options['name'];
        }

        $this->fields = [];

        if (empty($options['groups'])) {
            $options['groups'] = [];
        }

        if (!empty($options['elements'])) {
            $options['groups'][] = ['elements' => $options['elements']];
            unset($options['elements']);
        }

        foreach ($options['groups'] as $group_id => $group_info) {
            $fieldset_group = $group_info['belongsTo'] ?? null;
            $elements = [];

            foreach ($group_info['elements'] as $element_name => $element_info) {
                $field_type = strtolower($element_info[0]);
                $field_options = (array)$element_info[1];

                $element_group = $field_options['belongsTo'] ?? $fieldset_group ?? null;
                unset($field_options['belongsTo']);

                $element_key = $this->addField($element_name, $field_type, $field_options, $element_group);
                $elements[$element_key] = [$field_type, $field_options];
            }

            $options['groups'][$group_id]['elements'] = $elements;
        }

        $this->options = $options;
    }

    /**
     * Retrieve all of the current values set on the form.
     *
     * @return array
     */
    public function getValues(): array
    {
        $values = [];

        foreach ($this->options['groups'] as $fieldset) {
            foreach ($fieldset['elements'] as $element_id => $element_info) {
                if (isset($this->fields[$element_id])) {
                    $field = $this->fields[$element_id];

                    $value = $field->getValue();

                    if ($value !== null) {
                        $name = $field->getName();
                        $group = $field->getGroup();

                        if (null !== $group) {
                            $values[$group][$name] = $value;
                        } else {
                            $values[$name] = $value;
                        }
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Return the stored data for an individual field.
     *
     * @param string $key
     * @return null|mixed
     */
    public function getValue($key)
    {
        if (isset($this->fields[$key])) {
            return $this->fields[$key]->getValue();
        }
        return null;
    }

    /**
     * Find the appropriate class for the type specified.
     *
     * @param string $type
     * @return string
     * @throws Exception\FieldClassNotFound
     */
    protected function _getFieldClass($type): string
    {
        if (class_exists($type)) {
            return $type;
        }

        if (isset($this->field_name_conversions[$type])) {
            $type = $this->field_name_conversions[$type];
        }

        foreach ($this->field_namespaces as $namespace_option) {
            $field_class = $namespace_option.'\\'.ucfirst($type);

            if (class_exists($field_class)) {
                return $field_class;
                break;
            }
        }

        throw new Exception\FieldClassNotFound(sprintf('Input type "%s" not found.', $type));
    }

    /**
     * Render the entire form including submit button, errors, form tags etc.
     *
     * @return string
     */
    public function render(): string
    {
        $output = $this->openForm();

        if ($this->hasErrors()) {
            foreach($this->errors as $error) {
                $output .= '<div class="alert alert-danger" role="alert">'.$error.'</div>';
            }
        }

        foreach($this->options['groups'] as $fieldset_id => $fieldset) {
            $hide_fieldset = (bool)($fieldset['hide_fieldset'] ?? false);

            if (!$hide_fieldset) {
                $output .= sprintf('<fieldset id="%s" class="%s">',
                    $fieldset_id,
                    $fieldset['class'] ?? ''
                );

                if (!empty($fieldset['legend'])) {
                    $output .= '<legend class="'.$fieldset['legend_class'].'"><div>'.$fieldset['legend'].'</div></legend>';

                    if (!empty($fieldset['description'])) {
                        $output .= '<p class="'.$fieldset['description_class'].'">'.$fieldset['description'].'</p>';
                    }
                }
            }

            foreach($fieldset['elements'] as $element_id => $element_info) {
                if (isset($this->fields[$element_id])) {
                    $field = $this->fields[$element_id];
                    $output .= $field->render($this->name);
                }
            }

            if (!$hide_fieldset) {
                $output .= '</fieldset>';
            }
        }

        $output .= $this->renderHidden();

        $output .= $this->closeForm();
        return $output;
    }

    /**
     * Render the form in a presentation-only "view" mode, with no editable controls.
     *
     * @param bool $show_empty_fields
     * @return string
     */
    abstract public function renderView($show_empty_fields = false): string;

    /**
     * Returns HTML for all hidden fields.
     *
     * @return string
     */
    abstract public function renderHidden(): string;

    /**
     * Returns the HTML string for opening a form with the correct enctype, action and method
     *
     * @return string
     */
    abstract public function openForm(): string;

    /**
     * Return close form tag
     *
     * @return string
     */
    public function closeForm(): string
    {
        return '</form>';
    }
}
