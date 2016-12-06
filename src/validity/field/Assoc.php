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
            function($value, FieldSet $fieldSet) use ($innerFieldSet, $errorSeparator) {
                if (!is_array($value)) {
                    return false;
                }

                if ($innerFieldSet->isValid($value)) {
                    $fieldSet->getLastReport()->setFiltered($this->getName(), $value);
                    return true;
                } else {
                    $errorStr = join($errorSeparator, $innerFieldSet->getErrors()->toPlainArray($errorSeparator));
                    return $fieldSet->getLastReport()->addError($this->getName(), $errorStr);
                }
            },
            $message
        );
    }
}