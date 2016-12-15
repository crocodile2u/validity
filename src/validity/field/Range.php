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
     * @param bool $inclusive
     * @param string $message
     * @return $this
     */
    public function setMin($min, bool $inclusive = true, $message = null): Field
    {
        return $this->addRule(
            function() use ($min, $inclusive) {
                $comparison = $this->compareWith($min);
                return $inclusive ? ($comparison >= 0) : ($comparison > 0);
            },
            $message,
            $this->resolveMessageKeys()[0],
            ["min" => $min]
        );
    }

    /**
     * @param mixed $max
     * @param bool $inclusive
     * @param string $message
     * @return $this
     */
    public function setMax($max, bool $inclusive = true, $message = null): Field
    {
        return $this->addRule(
            function() use ($max, $inclusive) {
                $comparison = $this->compareWith($max);
                return $inclusive ? ($comparison <= 0) : ($comparison < 0);
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