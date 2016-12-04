<?php

namespace validity\field;

use validity\Field;
use validity\FieldSet;
use validity\Report;

class Assoc extends Field
{
    protected function __construct($name, FieldSet $innerFieldSet, $message = null, $errorSeparator = "; ")
    {
        parent::__construct($name, self::ANY, null);
        $this->addRule(
            function($name, $value, $message, Report $Report) use ($innerFieldSet, $errorSeparator) {
                if (!is_array($value)) {
                    return $Report->addError($name, $message);
                }

                if ($innerFieldSet->isValid($value)) {
                    return $innerFieldSet->getFiltered();
                } else {
                    $errors = join($errorSeparator, $innerFieldSet->getErrors()->toPlainArray($errorSeparator));
                    return $Report->addError($name, $errors);
                }
            },
            $message
        );
    }
}