<?php
namespace AzuraForms\Field;

use AzuraForms;

interface FieldInterface
{
    /**
     * Configure the field using the specified flat configuration.
     * @param array $config
     */
    public function configure(array $config = []): void;

    /**
     * @return AzuraForms\Form
     */
    public function getForm(): AzuraForms\Form;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Return the full element name, including element prefixes.
     *
     * @return string
     */
    public function getFullName(): string;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param mixed $new_value
     */
    public function setValue($new_value): void;

    /**
     * Clear all existing validators.
     */
    public function clearValidators(): void;

    /**
     * @param callable $validator
     */
    public function addValidator(callable $validator): void;

    /**
     * Clear all existing filters.
     */
    public function clearFilters(): void;

    /**
     * @param callable $filter
     */
    public function addFilter(callable $filter): void;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setOption($key, $value): void;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value): void;

    /**
     * Return a list of all current errors.
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Returns whether this element has any errors logged for it.
     *
     * @return bool
     */
    public function hasErrors(): bool;

    /**
     * Append a new error to the error log.
     *
     * @param mixed $body
     */
    public function addError($body): void;

    /**
     * Check the currently set value for validity.
     *
     * @param null $new_value
     * @return bool
     */
    public function isValid($new_value = null): bool;

    /**
     * Return an editable form control for this field.
     *
     * @param string $form_name
     * @return string The rendered form element.
     */
    public function render($form_name): string;

    /**
     * Return a view-only list version of the form element and its value.
     *
     * @param bool $show_empty
     * @return string
     */
    public function renderView($show_empty = false): string;

    /**
     * Return the field body HTML for this element.
     *
     * @param string $form_name
     * @return null|string
     */
    public function getField($form_name): ?string;

    /**
     * Return the label HTML for this element.
     *
     * @param string $form_name
     * @return null|string
     */
    public function getLabel($form_name): ?string;
}