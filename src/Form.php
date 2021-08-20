<?php
/**
 * AzuraForms Alpha
 *
 * Initially built upon Nibble Forms 2
 * Copyright (c) 2013 Luke Rotherfield, Nibble Development
 */

namespace AzuraForms;

use Psr\Http\Message\ServerRequestInterface;

class Form extends AbstractForm
{
    public const CSRF_FIELD_NAME = '_csrf';

    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';

    /** @var string The form's "action" attribute. */
    protected string $action = '';

    /** @var string The form's submission method (GET or POST). */
    protected string $method = self::METHOD_POST;

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
            $this->method = strtoupper($options['method']);
        }

        // Add CSRF field
        $this->addCsrfField();
    }

    /**
     * Add CSRF verification field to the form.
     */
    protected function addCsrfField(): void
    {
        $this->addField(self::CSRF_FIELD_NAME, Field\Csrf::class, [
            'csrf_key' => $this->name,
        ]);
    }

    /**
     * Set the already-filled data for this form.
     *
     * @param array $data
     * @param bool $clear_fields
     */
    public function populate(array $data = [], bool $clear_fields = false): void
    {
        if ($clear_fields) {
            foreach($this->fields as $field) {
                $field->clearValue();
            }
        }

        $set_data = [];

        foreach ($data as $row_key => $row_value) {
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
     * @param ServerRequestInterface $serverRequest
     *
     * @return bool Whether the submitted data is valid.
     */
    public function isValid(ServerRequestInterface $serverRequest): bool
    {
        if ($this->method !== $serverRequest->getMethod()) {
            return false;
        }

        $parsedBody = $serverRequest->getParsedBody();
        if (is_array($parsedBody)) {
            $this->populate($parsedBody, true);
        }

        $uploadedFiles = $serverRequest->getUploadedFiles();
        $this->populate($uploadedFiles);

        // Validate individual fields using the class validator.
        $is_valid = true;

        foreach ($this->fields as $key => $value) {
            if (!$value->isValid()) {
                $is_valid = false;
            }
        }

        return $is_valid;
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
                if (!empty($element_info[1]['belongsTo'])) {
                    $element_id = $element_info[1]['belongsTo'] . '_' . $element_id;
                }

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
        foreach ($this->fields as $field) {
            if ($field instanceof Field\Hidden) {
                $fields[] = $field->getField($this->name);
            }
        }

        return implode("\n", $fields);
    }

    /**
     * @inheritdoc
     */
    public function openForm(): string
    {
        $formAttrs = $this->getFormAttributes();

        $formAttrsStr = [];
        foreach($formAttrs as $key => $val) {
            $formAttrsStr[] = $key.'="' . htmlentities($val, ENT_QUOTES) . '"';
        }

        return '<form '.implode(' ', $formAttrsStr).'>';
    }

    protected function getFormAttributes(): array
    {
        $formAttrs = [
            'id' => $this->name,
            'method' => $this->method,
            'action' => $this->action,
            'class' => 'form '.($this->options['class'] ?? ''),
            'accept-charset' => 'UTF-8',
        ];

        foreach ($this->fields as $field) {
            if ($field instanceof Field\File) {
                $formAttrs['enctype'] = 'multipart/form-data';
                break;
            }
        }

        if (!empty($this->options['form'])) {
            $formAttrs = array_merge($formAttrs, (array)$this->options['form']);
        }

        return $formAttrs;
    }
}

