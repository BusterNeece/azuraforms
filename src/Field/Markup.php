<?php
namespace AzuraForms\Field;

/**
 * A generic "markup" field for including inline HTML in a form.
 */
final class Markup extends AbstractField
{
    public function getValue()
    {
        // Indicate that this field shouldn't be included in bulk value returns.
        return null;
    }

    public function getField($form_name): ?string
    {
        return $this->attributes['markup'];
    }

    public function isValid($new_value = null): bool
    {
        return true;
    }

    public function renderView($show_empty = false): string
    {
        return null;
    }
}