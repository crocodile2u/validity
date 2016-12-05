<?php

namespace validity\field;

class Date extends Timestamp
{
    use Range;

    const DEFAULT_FORMAT = "Y-m-d";

    protected function __construct($name, $typeMessage)
    {
        parent::__construct($name, $typeMessage, self::DATE);
    }

    /**
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    protected function compareValues($a, $b): int
    {
        $tsA = $this->toTimestamp($a);
        $tsB = $this->toTimestamp($b);
        return ((int) date("Ymd", $tsA)) <=> ((int) date("Ymd", $tsB));
    }
}