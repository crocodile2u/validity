<?php

namespace validity\field;

use validity\Field;

class Boolean extends Field
{
    use Range;

    protected function __construct($name, $typeMessage)
    {
        parent::__construct($name, self::BOOLEAN, $typeMessage);
    }

    protected function castToType($value)
    {
        if (is_bool($value)) {
            return $value;
        } elseif (is_numeric($value) && ((1 == $value) || (0 == $value))) {
            return (bool)$value;
        } else {
            $bool_str = strtoupper($value);

            $yes = ['YES', 'ON', 'TRUE'];
            if (in_array($bool_str, $yes)) {
                return true;
            }

            $no = ['NO', 'OFF', 'FALSE'];
            if (in_array($bool_str, $no)) {
                return false;
            }
        }

        return null;
    }
}