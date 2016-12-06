<?php

namespace validity\field;

use validity\Language;

class Email extends Str
{
    protected function __construct($name, $message = null)
    {
        parent::__construct($name, $message);
        $this->addRegexpRule(
            "~^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}\~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$~",
            $message,
            Language::EMAIL_EXPECTED
        );
    }
}