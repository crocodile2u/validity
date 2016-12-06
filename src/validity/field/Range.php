<?php

namespace validity\field;

use validity\Field;
use validity\Language;

trait Range
{
    /**
     * @var array
     */
    private static $languageKeyMap = [
        Integer::class => [Language::NUMBER_MIN, Language::NUMBER_MAX],
        Double::class => [Language::NUMBER_MIN, Language::NUMBER_MAX],
        Timestamp::class => [Language::DATE_MIN, Language::DATE_MAX],
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
            function() use ($min) {
                if ($this->compareWith($min) < 0) {
                    return false;
                } else {
                    return true;
                }
            },
            $message,
            $this->resolveMessageKeys()[0],
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
            function() use ($max) {
                if ($this->compareWith($max) > 0) {
                    return false;
                } else {
                    return true;
                }
            },
            $message,
            $this->resolveMessageKeys()[1],
            ["max" => $max]
        );
    }

    private function resolveMessageKeys()
    {
        foreach (self::$languageKeyMap as $class => $keys) {
            if ($this instanceof $class) {
                return $keys;
            }
        }
        return [Language::MIN, Language::MAX];
    }

    abstract protected function compareWith($value): int;
}