<?php

namespace validity\field;

use validity\Field;
use validity\Language;
use validity\Report;

class Str extends Field
{
    /**
     * Str constructor.
     * @param string $name
     * @param string $message
     */
    protected function __construct(string $name = null, $message)
    {
        parent::__construct($name, $message);
    }

    /**
     * @return int
     */
    protected function castToType($value)
    {
        if (is_scalar($value)) {
            $value = (string) $value;
        } else {
            return null;
        }
        $filteredValue = mb_convert_encoding($value, 'utf-8', 'utf-8');
        if ($filteredValue !== $value) {
            return $this->addError($this->predefinedMessage(Language::ILLEGAL_CHAR));
        }
        return $value;
    }

    /**
     * @param mixed $length
     * @param string $message
     * @return Field
     */
    public function setMinLength($length, $message = null): Str
    {
        return $this->addRule(
            function($value) use ($length) {
                if (mb_strlen($value) < $length) {
                    return false;
                } else {
                    return true;
                }
            },
            $message,
            Language::STRING_MIN_LEN,
            ["min" => $length]
        );
    }

    /**
     * @param mixed $max
     * @param string $message
     * @return Field
     */
    public function setMaxLength($length, $message = null): Str
    {
        return $this->addRule(
            function($value) use ($length) {
                if (mb_strlen($value) > $length) {
                    return false;
                } else {
                    return true;
                }
            },
            $message,
            Language::STRING_MAX_LEN,
            ["max" => $length]
        );
    }

    /**
     * @param string|$regexp
     * @param string|null $message
     * @param int $messageKey
     * @return $this
     */
    public function addRegexpRule($regexp, $message = null, $messageKey = Language::REGEXP_VALIDATION_FAILED): Field
    {
        return $this->addRule(
            function($value) use ($regexp) {
                if (preg_match($regexp, $value)) {
                    return true;
                } else {
                    return false;
                }
            },
            $message,
            $messageKey
        );
    }

    protected function preFilterStringValue($value)
    {
        return trim($value);
    }
}