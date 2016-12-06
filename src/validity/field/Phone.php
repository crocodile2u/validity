<?php

namespace validity\field;

use validity\Language;

class Phone extends Str
{
    /**
     * Phone constructor.
     * @param string $name
     * @param int $minLength
     * @param string $message
     */
    protected function __construct(string $name, $minLength = 7, $message = null)
    {
        parent::__construct($name, $message);
        $this->addFilter(
            function($value) {
                return preg_replace('/[+() -]/', '', $value);
            }
        )->addRegexpRule('/^\d{' . $minLength . ',20}$/', $message, Language::PHONE_EXPECTED);
    }
}