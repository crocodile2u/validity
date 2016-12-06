<?php

namespace validity\field;

use validity\Field;

class Boolean extends Field
{
    /**
     * Boolean constructor.
     * @param string $name
     * @param $message
     */
    protected function __construct(string $name, $message = null)
    {
        parent::__construct($name, $message);
    }

    /**
     * @param mixed $value
     * @return bool|null
     */
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