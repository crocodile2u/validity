<?php

namespace validity\field;

use validity\Field;

class Integer extends Field
{
    use Range;

    /**
     * Integer constructor.
     * @param string $name
     * @param string $message
     */
    protected function __construct(string $name = null, $message = null)
    {
        parent::__construct($name, $message);
    }

    /**
     * @return int
     */
    protected function castToType($value)
    {
        if ($value && ($value[0] === '+')) {
            $value = substr($value, 1);
        }
        if ($value !== '') {
            $value = ltrim($value, '0');
            if ($value === '') {
                $value = '0';
            }
        }
        if ('' === $value) {
            $value = '0';
        }
        if ((string)(int)$value !== $value) {
            return null;
        }
        return (int)$value;
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function compareWith($value): int
    {
        return $this->currentValue <=> $value;
    }
}