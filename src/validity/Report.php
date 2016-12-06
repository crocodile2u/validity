<?php

namespace validity;

class Report
{
    private $raw = array();
    private $filtered = array();
    private $errorSet;

    public function __construct($raw)
    {
        $this->raw = $raw;
        $this->errorSet = new MessageSet();
    }

    public function resetErrors($field)
    {
        $this->errorSet->reset($field);
        return $this;
    }

    public function isOk($key = null)
    {
        if (null === $key) {
            return 0 === count($this->errorSet);
        } else {
            return !$this->errorSet->offsetExists($key);
        }
    }

    public function isFailed($key = null)
    {
        return !$this->isOk($key);
    }

    public function addError($field, $message, $key = null)
    {
        $this->errorSet->add($field, $message, $key);
        return null;
    }

    public function setFiltered($key, $value)
    {
        $this->filtered[$key] = $value;
        return $this;
    }

    public function getFiltered($key = null)
    {
        if (null === $key) {
            return $this->filtered;
        } elseif (array_key_exists($key, $this->filtered)) {
            return $this->filtered[$key];
        } else {
            return null;
        }
    }

    public function getErrors($key = null)
    {
        return (null === $key) ? $this->errorSet : (isset($this->errorSet[$key]) ? $this->errorSet[$key] : null);
    }

    public function getRaw($key = null)
    {
        return (null === $key) ? $this->raw : ($this->inputKeyExists($key) ? $this->raw[$key] : null);
    }

    public function inputKeyExists($key)
    {
        return array_key_exists($key, $this->raw);
    }

    /**
     * Get filtered values for correctly filled data plus raw values for those that contain errors.
     * @param string $key
     * @return array
     */
    public function getMixed($key = null)
    {
        if (null === $key) {
            $filtered = array_filter($this->filtered, function($value) {
                return null !== $value;
            });
            return $filtered + $this->raw;
        } elseif (isset($this->errorSet[$key])) {
            return $this->getRaw($key);
        } else {
            return $this->getFiltered($key);
        }
    }
}
