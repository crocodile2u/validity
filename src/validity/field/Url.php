<?php

namespace validity\field;

class Url extends Str
{
    /**
     * Url constructor.
     * @param string $name
     * @param string $message
     */
    function __construct(string $name = null, $message = null)
    {
        parent::__construct($name, $message);
        $this->addRule(
            function($value) {
                $value = filter_var($value, FILTER_VALIDATE_URL);
                if ($value) {
                    $this->getReport()->setFiltered($this->getName(), $value);
                    return true;
                } else {
                    return false;
                }
            }
        );
    }

    /**
     * @param mixed $value
     * @return null
     */
    protected function castToType($value)
    {
        $value = parent::castToType($value);
        if ($value) {
            $value = filter_var($value, FILTER_VALIDATE_URL);
        }
        return $value ?: null;
    }
}