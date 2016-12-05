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
        if (is_string($value)) {
            if ("+" === $value[0]) {
                $value = substr($value, 1);
            }
            $value = ltrim($value, '0');
            $value = str_replace(',', '.', $value);
            if (substr_count($value, ".")) {
                if ("." === $value[0]) {
                    $value = "0" . $value;
                }
                $value = rtrim($value, '0.');
            }
            if ("" === $value) {
                $value = "0";
            }
            if (((string) (float) $value) !== $value) {
                return null;
            }
        }
        if (!is_numeric($value)) {
            return null;
        }
        return (float)$value;
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