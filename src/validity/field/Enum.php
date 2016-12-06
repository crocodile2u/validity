<?php

namespace validity\field;

use validity\Field;
use validity\Language;
use validity\Report;

class Enum extends Field
{
    protected function __construct($name, array $values, $message = null)
    {
        parent::__construct($name, self::ANY, null);
        $this->addRule(
            function($value) use ($values) {
                if (in_array($value, $values)) {
                    return true;
                } else {
                    return false;
                }
            },
            $message,
            Language::ENUM_VALIDATION_FAILED,
            ["values" => join(", ", $values)]
        );
    }
}