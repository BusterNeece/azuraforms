<?php
namespace AzuraForms\Field\Traits;

trait NullValueTrait
{
    public function getValue()
    {
        // Indicate that this field shouldn't be included in bulk value returns.
        return null;
    }

    public function setValue($new_value): void
    {
        // Don't allow the resetting of a submit value.
        return;
    }

    public function clearValue(): void
    {
        // Don't allow the resetting of a submit value.
        return;
    }
}