<?php

namespace validity\field;

class Datetime extends Timestamp
{
    use Range;

    const DEFAULT_OUTPUT_FORMAT = "Y-m-d H:i:s";
    const DEFAULT_INPUT_FORMAT = self::DEFAULT_OUTPUT_FORMAT;

    /**
     * Datetime constructor.
     * @param string $name
     * @param string $message
     */
    protected function __construct(string $name = null, $message = null)
    {
        parent::__construct($name, $message);
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