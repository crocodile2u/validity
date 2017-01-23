<?php

namespace validity\field;

use validity\Field;
use validity\Language;

class Enum extends Field
{
    /**
     * Enum constructor.
     * @param string $name
     * @param array $values
     * @param null $message
     */
    protected function __construct(string $name = null, array $values, $message = null)
    {
        parent::__construct($name, null);
        $this->addRule(
            function($value) use ($values) {
                return in_array($value, $values);
            },
            $message,
            Language::ENUM_VALIDATION_FAILED,
            ["values" => join(", ", $values)]
        );
    }
}