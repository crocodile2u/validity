<?php

namespace validity\field;

use validity\Field;
use validity\FieldSet;

class Assoc extends Field
{
    /**
     * Assoc constructor.
     * @param $name
     * @param FieldSet $innerFieldSet
     * @param null $message
     * @param string $errorSeparator
     */
    protected function __construct($name, FieldSet $innerFieldSet, $message = null, $errorSeparator = "; ")
    {
        parent::__construct($name, null);
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

    protected function isArray()
    {
        return true;
    }
}