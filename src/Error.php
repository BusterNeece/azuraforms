<?php
namespace AzuraForms;

class Error
{
    /** @var string */
    protected $message;

    /** @var string|null */
    protected $label;

    /**
     * @param string $message
     * @param string|null $label
     */
    public function __construct(string $message, ?string $label)
    {
        $this->message = $message;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function hasLabel(): bool
    {
        return !empty($this->label);
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }
}