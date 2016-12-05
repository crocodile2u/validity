<?php

namespace validity\field;

abstract class Timestamp extends Str
{
    use Range;

    const DEFAULT_FORMAT = "";

    protected $format;

    protected function __construct($name, $typeMessage, $refinedType)
    {
        parent::__construct($name, $typeMessage);
        $this->setType($refinedType);
        $this->format = static::DEFAULT_FORMAT;
    }

    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    protected function castToType($value)
    {
        $str = parent::castToType($value);
        if (!trim($value)) {
            return null;
        }
        $ts = strtotime($str);
        return $ts ? date($this->format, $ts) : null;
    }

    protected function toTimestamp($spec)
    {
        if (null === $spec) {
            return null;
        } elseif (is_int($spec)) {
            return $spec;
        } elseif ($spec instanceof \DateTime) {
            return $spec->getTimestamp();
        } elseif (is_string($spec)) {
            if ($ts = strtotime($spec)) {
                return $ts;
            } else {
                throw new \InvalidArgumentException("Cannot convert {$this->getName()} to timestamp");
            }
        }
    }

    protected function preFilterStringValue($value)
    {
        return $value;
    }
}