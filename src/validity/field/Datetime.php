<?php

namespace validity\field;

class Datetime extends Timestamp
{
    use Range;

    const DEFAULT_OUTPUT_FORMAT = "Y-m-d H:i:s";
    const DEFAULT_INPUT_FORMAT = self::DEFAULT_OUTPUT_FORMAT;

    protected function __construct($name, $typeMessage)
    {
        parent::__construct($name, $typeMessage, self::DATETIME);
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function compareWith($value): int
    {
        return $this->datetimeValue->getTimestamp() <=> $this->toTimestamp($value);
    }
}