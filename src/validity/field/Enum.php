<?php

namespace validity\field;

use validity\Field;
use validity\Language;
use validity\Report;

class Enum extends Field
{
    protected function __construct($name, array $values, $message)
    {
        parent::__construct($name, self::ANY, null);
        $this->addRule(
            function($name, $value, $message, Report $Result) use ($values) {
                if (in_array($value, $values)) {
                    return $value;
                } else {
                    return $Result->addError($name, $message);
                }
            },
            $message,
            Language::ENUM_VALIDATION_FAILED,
            ["values" => join(", ", $values)]
        );
    }
}