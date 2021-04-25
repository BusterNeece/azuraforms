<?php
namespace AzuraForms\Field\Traits;

trait NullValueTrait
{
    /**
     * @return mixed
     */
    public function getValue()
    {
        // Indicate that this field shouldn't be included in bulk value returns.
        return null;
    }

    public function setValue($new_value): void
    {
        // Don't allow the resetting of a submit value.
    }

    public function clearValue(): void
    {
        // Don't allow the resetting of a submit value.
    }
}
