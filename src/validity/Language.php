<?php

namespace validity;

use validity\language\En;

abstract class Language
{
    const REQUIRED = 1,
        ARRAY_EXPECTED = 2,
        SCALAR_EXPECTED = 3,
        ILLEGAL_CHAR = 4,
        INT = 5,
        NUMBER_MIN = 6,
        NUMBER_MAX = 7,
        STRING = 8,
        STRING_MIN_LEN = 9,
        STRING_MAX_LEN = 10,
        FLOAT = 11,
        BOOL = 12,
        FIELD_FAILED_VALIDATION = 13,
        ASSOC_EXPECTED = 14,
        DATETIME_EXPECTED = 15,
        DATE_EXPECTED = 16,
        EMAIL_EXPECTED = 17,
        PHONE_EXPECTED = 18,
        REGEXP_VALIDATION_FAILED = 19,
        ENUM_VALIDATION_FAILED = 20,
        DATE_MIN = 21,
        DATE_MAX = 22,
        ARRAY_MIN_LEN = 23,
        ARRAY_MAX_LEN = 24;
    /**
     * @var self
     */
    static private $instance;

    /**
     * @var string[]
     */
    protected $messages;

    static function getDefault()
    {
        return new En;
    }

    /**
     * @param Language $language
     */
    static function setInstance(self $language)
    {
        self::$instance = $language;
    }

    /**
     * @return Language
     */
    static function getInstance()
    {
        if (null === self::$instance) {
            self::setInstance(self::getDefault());
        }
        return self::$instance;
    }

    /**
     * @param string $key
     * @param string[] $data
     * @return mixed
     */
    public function translate(string $key, array $data = [])
    {
        if (array_key_exists($key, $this->messages)) {
            return $this->message($this->messages[$key], $data);
        } else {
            throw new \InvalidArgumentException("Unknown message key " . $key);
        }
    }

    /**
     * @param $template
     * @param array $data
     * @return mixed
     */
    public function message($template, array $data = [])
    {
        $message = preg_replace_callback(
            "/\{([a-z]+)\}/",
            function($m) use ($data) {
                $dataKey = $m[1];
                if (array_key_exists($dataKey, $data)) {
                    return $data[$dataKey];
                } else {
                    return $m[0];
                }
            },
            $template
        );
        return $message;
    }
}