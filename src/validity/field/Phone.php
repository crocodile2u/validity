<?php

namespace validity\field;

use validity\Language;

class Phone extends Str
{
    protected function __construct($name, $minLength = 7, $message = null)
    {
        parent::__construct($name, $message);
        $this->addFilter(
            function($value) {
                return preg_replace('/[+() -]/', '', $value);
            }
        )->addRegexpRule('/^\d{' . $minLength . ',20}$/', $message, Language::PHONE_EXPECTED);
    }
}