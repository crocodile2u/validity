<?php

namespace validity\field;

class Datetime extends Timestamp
{
    use Range;

    const DEFAULT_FORMAT = "Y-m-d H:i:s";

    protected function __construct($name, $typeMessage)
    {
        parent::__construct($name, $typeMessage, self::DATETIME);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    protected function compareValues($a, $b): int
    {
        return $this->toTimestamp($a) <=> $this->toTimestamp($b);
    }
}