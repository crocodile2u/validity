<?php

namespace validity\field;

class Date extends Timestamp
{
    use Range;

    const DEFAULT_OUTPUT_FORMAT = "Y-m-d";
    const DEFAULT_INPUT_FORMAT = "d.m.Y";

    /**
     * Date constructor.
     * @param string $name
     * @param string $message
     */
    protected function __construct(string $name, $message = null)
    {
        parent::__construct($name, $message);
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function compareWith($value): int
    {
        $tsA = $this->datetimeValue->getTimestamp();
        $tsB = $this->toTimestamp($value);
        return ((int) date("Ymd", $tsA)) <=> ((int) date("Ymd", $tsB));
    }
}