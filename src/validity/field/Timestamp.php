<?php

namespace validity\field;

abstract class Timestamp extends Str
{
    use Range;

    const DEFAULT_OUTPUT_FORMAT = "";
    const DEFAULT_INPUT_FORMAT = "";

    protected $outputFormat;
    protected $inputFormat;
    protected $inputFormatStrict = true;
    /**
     * @var \DateTimeZone
     */
    protected $timezone;
    /**
     * @var \DateTime
     */
    protected $datetimeValue;

    protected function __construct($name, $typeMessage, $refinedType)
    {
        parent::__construct($name, $typeMessage);
        $this->setType($refinedType);
        $this->outputFormat = static::DEFAULT_OUTPUT_FORMAT;
        $this->inputFormat = static::DEFAULT_INPUT_FORMAT;
    }

    public function setOutputFormat($outputFormat)
    {
        $this->outputFormat = $outputFormat;
        return $this;
    }

    public function setInputFormat($inputFormat, $strict = true)
    {
        $this->inputFormat = $inputFormat;
        $this->inputFormatStrict = $strict;
        return $this;
    }

    /**
     * @param \DateTimeZone $timezone
     * @return Timestamp
     */
    public function setTimezone(\DateTimeZone $timezone): Timestamp
    {
        $this->timezone = $timezone;
        return $this;
    }

    protected function castToType($value)
    {
        $value = parent::castToType($value);
        if (!trim($value)) {
            return null;
        }
        $datetime = \DateTime::createFromFormat($this->inputFormat, $value, $this->timezone);
        if ($datetime) {
            $this->datetimeValue = $datetime;
            return $datetime->format($this->outputFormat);
        } elseif ($this->inputFormatStrict) {
            return null;
        } else {
            $datetime = date_create($value);
            if ($datetime) {
                $this->datetimeValue = $datetime;
                return $datetime->format($this->outputFormat);
            } else {
                return null;
            }
        }
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
}