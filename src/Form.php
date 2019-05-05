<?php
/**
 * AzuraForms Alpha
 *
 * Initially built upon Nibble Forms 2
 * Copyright (c) 2013 Luke Rotherfield, Nibble Development
 */

namespace AzuraForms;

class Form extends AbstractForm
{
    public const CSRF_FIELD_NAME = '_csrf';

    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';

    /** @var string The form's "action" attribute. */
    protected $action = '';

    /** @var string The form's submission method (GET or POST). */
    protected $method = self::METHOD_POST;

    /**
     * @param array $options
     * @param array|null $defaults
     * @throws Exception\FieldAlreadyExists
     * @throws Exception\FieldClassNotFound
     */
    public function __construct(array $options = [], ?array $defaults = null)
    {
        parent::__construct($options);

        if ($defaults !== null) {
            $this->populate($defaults);
        }
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get form method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function configure(array $options): void
    {
        $this->groups = [];

        parent::configure($options);

        if (isset($options['action'])) {
            $this->action = $options['action'];
        }

        if (isset($options['method'])) {
            $this->method = $options['method'];
        }

        // Add CSRF field
        $this->addField(self::CSRF_FIELD_NAME, Field\Csrf::class, [
            'csrf_key' => $this->name,
        ]);
    }

    /**
     * Set the already-filled data for this form.
     *
     * @param array $data
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
        $is_valid = true;

        foreach ($this->fields as $key => $value) {
            if (!$value->isValid($request[$key] ?? $file_data[$key] ?? '')) {
                $is_valid = false;
            }
        }

        return $is_valid;
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
    protected function _fixFilesArray($files): array
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
     * @inheritdoc
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
                $output .= '<legend class="'.$fieldset['legend_class'].'">'.$fieldset['legend'].'</legend>';

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
     * @inheritdoc
     */
    public function renderHidden(): string
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
     * @inheritdoc
     */
    public function openForm(): string
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

        return sprintf('<form id="%s" action="%s" method="%s" class="%s" %s>',
            $this->name,
            $this->action,
            $this->method,
            $class,
            $enctype);
    }
}

