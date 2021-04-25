<?php
namespace AzuraForms\Field;

/**
 * A generic "markup" field for including inline HTML in a form.
 */
class Markup extends AbstractField
{
    use Traits\NullValueTrait;

    public function getField(string $form_name): ?string
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
