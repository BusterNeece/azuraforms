<?php
/**
 * AzuraForms Alpha
 *
 * Initially built upon Nibble Forms 2
 * Copyright (c) 2013 Luke Rotherfield, Nibble Development
 */

namespace AzuraForms;

class Form
{
    public const DEFAULT_FORM_NAME = 'azuraforms_form';
    public const CSRF_FIELD_NAME = '_csrf';

    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';

    /** @var array The form's configuration options. */
    protected $options;

    /** @var array An array of "belongsTo" lookup groups (for processing data). */
    protected $groups;

    /** @var Field\AbstractField[] */
    protected $fields;

    /** @var string The form's "action" attribute. */
    protected $action = '';

    /** @var string The form's submission method (GET or POST). */
    protected $method = self::METHOD_POST;

    /** @var string The form name, used in the <form> tag and in general. */
    protected $name = self::DEFAULT_FORM_NAME;

    /**
     * Form constructor.
     *
     * @param array $options
     * @param array|null $defaults
     */
    public function __construct(array $options = [], array $defaults = null)
    {
        $this->configure($options);

        if ($defaults !== null) {
            $this->populate($defaults);
        }
    }

    /**
     * Set the name of the form
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get form name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get form method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the cleaned-up flatfile configuration for this form.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Retrieve an already added field.
     *
     * @param $key
     * @return mixed
     */
    public function getField($key)
    {
        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        }

        throw new \Exception(sprintf('Field name "%s" not found.', $key));
    }

    /**
     * Iterate through configuration options and set up each individual form element.
     *
     * @param array $options
     */
    public function configure(array $options)
    {
        $this->name = $options['name'] ?: 'app_form';
        $this->action = $options['action'] ?: '';

        $this->fields = [];
        $this->groups = [];

        if (empty($options['groups'])) {
            $options['groups'] = [];
        }

        if (!empty($options['elements'])) {
            $options['groups'][] = ['elements' => $options['elements']];
            unset($options['elements']);
        }

        foreach ($options['groups'] as $group_id => $group_info) {
            foreach ($group_info['elements'] as $element_name => $element_info) {
                $this->_configureElement($element_name, $element_info);
            }
        }

        $this->options = $options;

        // Add CSRF field
        $this->addField(self::CSRF_FIELD_NAME, Field\Csrf::class, [
            'csrf_key' => $this->name,
        ]);
    }

    /**
     * Load a form element's configuration and instantiate it as an object.
     *
     * @param $element_name
     * @param $element_info
     */
    protected function _configureElement($element_name, $element_info): void
    {
        $field_type = strtolower($element_info[0]);
        $field_options = (array)$element_info[1];

        if (!empty($field_options['belongsTo'])) {
            $group = $field_options['belongsTo'];
            $this->groups[$group][] = $element_name;

            $element_name = $group . '_' . $element_name;
            unset($field_options['belongsTo']);
        }

        $this->addField($element_name, $field_type, $field_options);
    }

    /**
     * Set the already-filled data for this form.
     *
     * @param $data
     */
    public function populate($data)
    {
        $set_data = [];

        foreach ((array)$data as $row_key => $row_value) {
            if (is_array($row_value) && isset($this->groups[$row_key])) {
                foreach ($row_value as $row_subkey => $row_subvalue) {
                    $set_data[$row_key . '_' . $row_subkey] = $row_subvalue;
                }
            } else {
                $set_data[$row_key] = $row_value;
            }
        }

        foreach ($set_data as $field_name => $field_value) {
            if (isset($this->fields[$field_name])) {
                $field = $this->fields[$field_name];
                $field->setValue($field_value);
            }
        }
    }

    /**
     * Retrieve all of the current values set on the form.
     *
     * @return array
     */
    public function getValues()
    {
        $values = [];

        foreach ($this->options['groups'] as $fieldset) {
            foreach ($fieldset['elements'] as $element_id => $element_info) {
                if (!empty($element_info[1]['belongsTo'])) {
                    $group = $element_info[1]['belongsTo'];
                    $value = $this->getValue($group . '_' . $element_id);

                    if ($value !== null) {
                        $values[$group][$element_id] = $value;
                    }
                } else {
                    $value = $this->getValue($element_id);

                    if ($value !== null) {
                        $values[$element_id] = $value;
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Return the stored data for an individual field.
     *
     * @param $key
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
     * Add a field to the form instance
     *
     * @param string $field_name
     * @param string $type
     * @param array $attributes
     * @param boolean $overwrite
     *
     * @return Field\AbstractField
     */
    public function addField($field_name, $type = 'text', array $attributes = [], $overwrite = false): Field\AbstractField
    {
        $class = $this->_getFieldClass($type);

        if (isset($this->fields[$field_name]) && !$overwrite) {
            throw new \Exception(sprintf('Input type "%s" already exists.', $type));
        }

        $this->fields[$field_name] = new $class($this, $field_name, $attributes);
        return $this->fields[$field_name];
    }

    /**
     * Find the appropriate class for the type specified.
     *
     * @param $type
     * @return string
     */
    protected function _getFieldClass($type): string
    {
        if (class_exists($type)) {
            return $type;
        }

        $field_type_lookup = [
            'checkboxes'    => Field\Checkbox::class,
            'multicheckbox' => Field\Checkbox::class,
            'multiselect'   => Field\MultipleSelect::class,
            'textarea'      => Field\TextArea::class,
        ];

        if (isset($field_type_lookup[$type])) {
            return $field_type_lookup[$type];
        }

        if (!isset($class)) {
            $namespace_options = [
                "\\AzuraForms\\Field\\" . ucfirst($type),
            ];

            foreach ($namespace_options as $namespace_option) {
                if (class_exists($namespace_option)) {
                    return $namespace_option;
                    break;
                }
            }
        }

        throw new \Exception(sprintf('Input type "%s" not found.', $type));
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
     * Validate the submitted form.
     *
     * @param array|null $request
     * @return bool
     */
    public function isValid(array $request = null): bool
    {
        if ($request === null) {
            $request = (strtoupper($this->method) === self::METHOD_POST)
                ? (array)$_POST
                : (array)$_GET;
        }

        $file_data = $this->_fixFilesArray($_FILES ?? array());

        if (empty($request)) {
            return false;
        }

        // Validate individual fields using the class validator.
        foreach ($this->fields as $key => $value) {
            if (!$value->isValid($request[$key] ?? $file_data[$key] ?? '')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fixes the odd indexing of multiple file uploads from the format:
     * $_FILES['field']['key']['index']
     *
     * To the more standard and appropriate:
     * $_FILES['field']['index']['key']
     *
     * @param array $files
     * @return array
     * @author Corey Ballou
     * @link http://www.jqueryin.com
     */
    protected function _fixFilesArray($files)
    {
        $names = array('name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

        foreach ($files as $key => $part) {
            $key = (string) $key;
            if (isset($names[$key]) && is_array($part)) {
                foreach ($part as $position => $value) {
                    $files[$position][$key] = $value;
                }
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * Render the entire form including submit button, errors, form tags etc
     *
     * @return string
     */
    public function render(): string
    {
        $output = $this->openForm();

        foreach($this->options['groups'] as $fieldset_id => $fieldset) {
            if (!empty($fieldset['legend'])) {
                $output .= sprintf('<fieldset id="%s" class="%s">',
                    $fieldset_id,
                    $fieldset['class'] ?? ''
                );
                $output .= '<legend>'.$fieldset['legend'].'</legend>';

                if (!empty($fieldset['description'])) {
                    $output .= '<p>'.$fieldset['description'].'</p>';
                }
            }

            foreach($fieldset['elements'] as $element_id => $element_info) {
                if (!empty($element_info[1]['belongsTo']))
                    $element_id = $element_info[1]['belongsTo'].'_'.$element_id;

                if (isset($this->fields[$element_id])) {
                    $field = $this->fields[$element_id];
                    $output .= $field->render($this->name);
                }
            }

            if (!empty($fieldset['legend'])) {
                $output .= '</fieldset>';
            }
        }

        $output .= $this->fields[self::CSRF_FIELD_NAME]->render($this->name);

        $output .= $this->closeForm();
        return $output;
    }

    /**
     * Render the form in a presentation-only "view" mode, with no editable controls.
     *
     * @param bool $show_empty_fields
     * @return string
     */
    public function renderView($show_empty_fields = false): string
    {
        $output = '';

        foreach($this->options['groups'] as $fieldset_id => $fieldset) {
            if (!empty($fieldset['legend'])) {
                $output .= sprintf('<fieldset id="%s" class="%s">',
                    $fieldset_id,
                    $fieldset['class'] ?? ''
                );
                $output .= '<legend>'.$fieldset['legend'].'</legend>';

                if (!empty($fieldset['description'])) {
                    $output .= '<p>'.$fieldset['description'].'</p>';
                }
            }

            $output .= '<dl>';

            foreach($fieldset['elements'] as $element_id => $element_info) {
                if (!empty($element_info[1]['belongsTo']))
                    $element_id = $element_info[1]['belongsTo'].'_'.$element_id;

                if (isset($this->fields[$element_id])) {
                    $field = $this->fields[$element_id];
                    $output .= $field->renderView($show_empty_fields);
                }
            }

            $output .= '</dl>';

            if (!empty($fieldset['legend'])) {
                $output .= '</fieldset>';
            }
        }

        return $output;
    }

    /**
     * Returns HTML for all hidden fields including crsf protection
     *
     * @return string
     */
    public function renderHidden()
    {
        $fields = array();
        foreach ($this->fields as $name => $field) {
            if ($field instanceof Field\Hidden) {
                $fields[] = $field->getField($this->name);
            }
        }
        $fields[] = $this->fields[self::CSRF_FIELD_NAME]->getField($this->name);

        return implode("\n", $fields);
    }

    /**
     * Returns the HTML string for opening a form with the correct enctype, action and method
     *
     * @return string
     */
    public function openForm()
    {
        $class = 'form';
        if (isset($this->options['class'])) {
            $class .= ' '.$this->options['class'];
        }

        $enctype = '';
        foreach ($this->fields as $field) {
            if ($field instanceof Field\File) {
                $enctype = 'enctype="multipart/form-data"';
            }
        }

        return sprintf('<form class="form" action="%s" method="%s" class="%s" %s>',
            $this->action,
            $this->method,
            $class,
            $enctype);
    }

    /**
     * Return close form tag
     *
     * @return string
     */
    public function closeForm()
    {
        return "</form>";
    }
}

