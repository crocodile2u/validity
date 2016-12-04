<?php

namespace validity\field;

use validity\Field;

class Double extends Field
{
    use Range;

    protected function __construct($name, $typeMessage)
    {
        parent::__construct($name, self::FLOAT, $typeMessage);
    }

    /**
     * @return int
     */
    protected function castToType($value)
    {
        $value = str_replace([',', ' '], ['.', ''], $value);
        if (!is_numeric($value)) {
            return null;
        }
        return (float)$value;
    }
}