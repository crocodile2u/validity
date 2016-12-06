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

    /**
     * Timestamp constructor.
     * @param string $name
     * @param string $message
     */
    protected function __construct(string $name, $message)
    {
        parent::__construct($name, $message);
        $this->outputFormat = static::DEFAULT_OUTPUT_FORMAT;
        $this->inputFormat = static::DEFAULT_INPUT_FORMAT;
    }

    /**
     * @param string $outputFormat
     * @return $this
     */
    public function setOutputFormat(string $outputFormat)
    {
        $this->outputFormat = $outputFormat;
        return $this;
    }

    /**
     * @param string $inputFormat
     * @param bool $strict
     * @return $this
     */
    public function setInputFormat(string $inputFormat, bool $strict = true)
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

    /**
     * @param mixed $value
     * @return string
     */
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

    /**
     * @param mixed $spec
     * @return false|int|null
     * @throws \InvalidArgumentException
     */
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
                throw new \InvalidArgumentException("Cannot convert value to timestamp: " . var_export($spec, 1));
            }
        }
    }
}