<?php

namespace validity\field;

use validity\Field;
use validity\Language;
use validity\Report;

trait Range
{
    private static $languageKeyMap = [
        Field::INT => [Language::NUMBER_MIN, Language::NUMBER_MAX],
        Field::FLOAT => [Language::NUMBER_MIN, Language::NUMBER_MAX],
        Field::DATE => [Language::DATE_MIN, Language::DATE_MAX],
        Field::DATETIME => [Language::DATE_MIN, Language::DATE_MAX],
    ];

    /**
     * @param mixed $min
     * @param string $message
     * @return $this
     */
    public function setMin($min, $message = null): Field
    {
        /** @var Field $this */
        return $this->addRule(
            function($name, $value, $message, Report $Report) use ($min) {
                if ($this->compareValues($value, $min) < 0) {
                    return $Report->addError($name, $message);
                } else {
                    return $value;
                }
            },
            $message,
            self::$languageKeyMap[$this->getType()][0],
            ["min" => $min]
        );
    }

    /**
     * @param mixed $max
     * @param string $message
     * @return $this
     */
    public function setMax($max, $message = null): Field
    {
        return $this->addRule(
            function($name, $value, $message, Report $Report) use ($max) {
                if ($this->compareValues($value, $max) > 0) {
                    return $Report->addError($name, $message);
                } else {
                    return $value;
                }
            },
            $message,
            self::$languageKeyMap[$this->getType()][1],
            ["max" => $max]
        );
    }
}