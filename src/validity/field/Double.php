<?php

namespace validity\field;

use validity\Field;

class Double extends Field
{
    use Range;

    /**
     * Double constructor.
     * @param string $name
     * @param string $message
     */
    protected function __construct(string $name, $message = null)
    {
        parent::__construct($name, $message);
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