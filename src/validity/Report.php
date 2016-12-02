<?php

namespace validity;

class Report
{
    private $raw = array();
    private $filtered = array();
    private $errors = array();

    public function __construct($raw)
    {
        $this->raw = $raw;
    }

    public function resetErrors($field)
    {
        unset($this->errors[$field]);
        return $this;
    }

    public function isOk($key = null)
    {
        if (null === $key) {
            return 0 === count($this->errors);
        } else {
            return !array_key_exists($key, $this->errors);
        }
    }

    public function isFailed($key = null)
    {
        return !$this->isOk($key);
    }

    public function addError($field, $message)
    {
        $this->errors[$field] = $message;
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
        return (null === $key) ? $this->errors : (isset($this->errors[$key]) ? $this->errors[$key] : null);
    }

    public function getRaw($key = null)
    {
        return (null === $key) ? $this->raw : (isset($this->raw[$key]) ? $this->raw[$key] : null);
    }
}
