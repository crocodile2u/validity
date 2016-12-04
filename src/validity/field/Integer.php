<?php

namespace validity\field;

use validity\Field;

class Integer extends Field implements RangeAware
{
    use Range;

    protected function __construct($name, $typeMessage)
    {
        parent::__construct($name, self::INT, $typeMessage);
    }

    /**
     * @return int
     */
    protected function castToType($value)
    {
        if ($value && ($value[0] === '+')) {
            $value = mb_substr($value, 1);
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
}