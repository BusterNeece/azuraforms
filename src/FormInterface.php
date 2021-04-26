<?php
namespace AzuraForms;

use IteratorAggregate;

interface FormInterface extends IteratorAggregate
{
    /**
     * Set the name of the form
     *
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * Get form name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Iterate through configuration options and set up each individual form element.
     *
     * @param array $options
     * @throws Exception\FieldAlreadyExists
     * @throws Exception\FieldClassNotFound
     */
    public function configure(array $options): void;

    /**
     * Get the cleaned-up flatfile configuration for this form.
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Retrieve an already added field.
     *
     * @param string $key
     *
     * @return Field\FieldInterface
     * @throws Exception\FieldNotFound
     */
    public function getField(string $key): Field\FieldInterface;

    /**
     * Check if a field exists
     *
     * @param string $field
     *
     * @return boolean
     */
    public function hasField(string $field): bool;

    /**
     * Add a field to the form instance.
     *
     * @param string $field_name
     * @param string $type
     * @param array $attributes
     * @param string|null $group
     * @param bool $overwrite
     *
     * @return string The finalized (and group-prefixed) element name for the element.
     * @throws Exception\FieldAlreadyExists
     * @throws Exception\FieldClassNotFound
     */
    public function addField(string $field_name, $type = 'text', array $attributes = [], ?string $group = null, $overwrite = false): string;

    /**
     * Retrieve all of the current values set on the form.
     *
     * @return array
     */
    public function getValues(): array;

    /**
     * Return the stored data for an individual field.
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public function getValue(string $key);

    /**
     * Render the entire form including submit button, errors, form tags etc.
     *
     * @return string
     */
    public function render(): string;

    /**
     * Render the form in a presentation-only "view" mode, with no editable controls.
     *
     * @param bool $show_empty_fields
     * @return string
     */
    public function renderView($show_empty_fields = false): string;

    /**
     * Returns HTML for all hidden fields.
     *
     * @return string
     */
    public function renderHidden(): string;

    /**
     * Returns the HTML string for opening a form with the correct enctype, action and method
     *
     * @return string
     */
    public function openForm(): string;

    /**
     * Return close form tag
     *
     * @return string
     */
    public function closeForm(): string;
}
